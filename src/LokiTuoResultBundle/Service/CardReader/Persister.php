<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 10.08.16
 * Time: 13:15
 */

namespace LokiTuoResultBundle\Service\CardReader;

use Doctrine\ORM\EntityManager;
use LokiTuoResultBundle\Entity\Card;
use LokiTuoResultBundle\Entity\CardFile;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Persister
{
    /** @var EntityManager */
    private $em;

    /** @var  LoggerInterface */
    private $logger;

    private $persist = true;

    /**
     * Persister constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->logger = new NullLogger();
    }

    public function importCards()
    {
        $criteria = ['status' => CardFile::STATUS_NOT_IMPORTED];
        $files = $this->em->getRepository('LokiTuoResultBundle:CardFile')->findBy($criteria);
        $cards = [];
        foreach ($files as $file) {
            $content = simplexml_load_string($file->getContent());
            $cards = $this->transformToModels($content, $file, $cards);
            $this->persistModels($cards);
        }
        $this->em->flush();
    }

    /**
     * @param $content
     * @return Card[]
     */
    private function transformToModels($content, CardFile $file, $result = [])
    {
        foreach ($content as $object) {
            var_dump(trim($object->name));
            if (array_key_exists(trim($object->name), $result)) {
                continue;
            }
            $card = new Card();
            $card->setName(trim($object->name));
            $card->setPicture($object->picture);
            $card->setAttack($object->attack);
            $card->setDefense($object->health);
            $card->setDelay($object->cost);
            $card->setCardFile($file);
            if (isset($object->upgrade)) {
                foreach ($object->upgrade as $upgrade) {
                    if (isset($upgrade->picture)) {
                        $card->setPicture($upgrade->picture);
                    }
                    if (isset($upgrade->health)) {
                        $card->setDefense($upgrade->health);
                    };
                    if (isset($upgrade->attack)) {
                        $card->setAttack($upgrade->attack);
                    };
                    if (isset($upgrade->cost)) {
                        $card->setDelay($upgrade->cost);
                    }
                }
            } else {
//                $this->logger->debug('Card without Upgrade found: ' . $card->getName());
            }
            $result[$card->getName()] = $card;
        }
        return $result;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param Card[] $cards
     * @return mixed
     */
    private function persistModels($cards)
    {
        if (!$this->persist) {
            return $cards;
        }
        $cardRepo = $this->em->getRepository('LokiTuoResultBundle:Card');
        foreach ($cards as $key => $card) {
            $dbEntity = $cardRepo->findOneBy(['name' => $card->getName()]);

            if ($dbEntity) {
                $card->setId($dbEntity->getId());
                $dbEntity->setPicture($card->getPicture());
                $dbEntity->setDelay($card->getDelay());
                $dbEntity->setDefense($card->getDefense());
                $dbEntity->setCardFile($card->getCardFile());
                $dbEntity->setAttack($card->getAttack());
                $this->em->persist($dbEntity);
//                $this->logger->debug("Duplicate Card found: " . $card->getName() . " With id " . $dbEntity->getId());
            } else {
                $this->em->persist($card);
            }
        }
        return true;
    }
}
