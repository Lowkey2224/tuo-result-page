<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 10.08.16
 * Time: 13:15.
 */

namespace LokiTuoResultBundle\Service\CardReader;

use Doctrine\ORM\EntityManager;
use LokiTuoResultBundle\Entity\Card;
use LokiTuoResultBundle\Entity\CardFile;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

class Persister
{
    /** @var EntityManager */
    private $em;

    use LoggerAwareTrait;

    private $persist = true;

    /**
     * Persister constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em     = $em;
        $this->logger = new NullLogger();
    }

    public function importCards()
    {
        $criteria    = ['status' => CardFile::STATUS_NOT_IMPORTED];
        $files       = $this->em->getRepository('LokiTuoResultBundle:CardFile')->findBy($criteria);
        $transformer = new Transformer();
        $transformer->setLogger($this->logger);
        $cards     = [];
        $cardCount = 0;
        foreach ($files as $file) {
            $content = simplexml_load_string($file->getContent());
            $cards   = $transformer->transformToModels($content, $file, $cards);
        }
        $cardCount += $this->persistModels($cards);
        $this->em->flush();
        foreach ($files as $file) {
            $file->setStatus(CardFile::STATUS_IMPORTED);
            $this->em->persist($file);
        }
        $this->em->flush();

        return $cardCount;
    }

    /**
     * @param Card[] $cards
     *
     * @return mixed
     */
    private function persistModels($cards)
    {
        $count = 0;
        if (! $this->persist) {
            return $cards;
        }
        $cardRepo = $this->em->getRepository('LokiTuoResultBundle:Card');
        foreach ($cards as $key => $card) {
            $dbEntity = $cardRepo->findOneBy(['name' => $card->getName()]);
            ++$count;
            $this->logger->error("Persisting card number $card with name".$card->getName());
            if ($dbEntity) {
                $card->setId($dbEntity->getId());
                $dbEntity->setPicture($card->getPicture());
                $dbEntity->setDelay($card->getDelay());
                $dbEntity->setDefense($card->getDefense());
                $dbEntity->setCardFile($card->getCardFile());
                $dbEntity->setAttack($card->getAttack());
                $dbEntity->setSkills($card->getSkills());
                $dbEntity->setRace($card->getRace());
                $this->em->persist($dbEntity);
//                $this->logger->debug("Duplicate Card found: " . $card->getName() . " With id " . $dbEntity->getId());
            } else {
                $this->em->persist($card);
            }
        }

        return $count;
    }
}
