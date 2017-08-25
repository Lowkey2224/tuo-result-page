<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 10.08.16
 * Time: 13:15.
 */

namespace LokiTuoResultBundle\Service\CardReader;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use LokiTuoResultBundle\Entity\Card;
use LokiTuoResultBundle\Entity\CardFile;
use LokiTuoResultBundle\Entity\CardLevel;
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
        $cardCount = 0;
        foreach ($files as $file) {
            $this->logger->info(sprintf("Reading file %s ...", $file->getOriginalFileName()));
            $content = simplexml_load_string($file->getContent());
            $cards   = $transformer->transformToModels($content, $file);
            $this->logger->info(sprintf("Finished reading file %s ...", $file->getOriginalFileName()));
            $cardCount += $this->persistModels($cards);
            $this->em->flush();
            $file->setStatus(CardFile::STATUS_IMPORTED);
            $this->em->persist($file);
            $this->em->flush();

        }

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
        if (!$this->persist) {
            return $cards;
        }
        $cardRepo = $this->em->getRepository('LokiTuoResultBundle:Card');
        foreach ($cards as $key => $card) {
            $dbEntity = $cardRepo->findOneBy(['name' => $card->getName()]);
            ++$count;
            $this->logger->debug("Persisting card number $card with name " . $card->getName());
            $dbEntity = $this->updateCard($card, $dbEntity);
            $this->em->persist($dbEntity);
        }
        return $count;
    }

    private function updateCard(Card $newCard, Card $oldCard = null)
    {
        if ($oldCard instanceof Card) {
            $oldCard->setName($newCard->getName());
            $oldCard->setCardFile($newCard->getCardFile());
            $oldCard->setRace($newCard->getRace());
            $oldCard->setLevels($this->updateLevels($newCard, $oldCard));
            return $oldCard;
        }
        return $newCard;
    }

    private function updateLevels(Card $newCard, Card $oldCard)
    {
        $levels = new ArrayCollection();
        foreach ($newCard->getLevels() as $level) {
            $old = $oldCard->getLevel($level->getTuoId());
            if($old instanceof CardLevel) {
                $old->setDelay($level->getDelay());
                $old->setPicture($level->getPicture());
                $old->setDefense($level->getDefense());
                $old->setAttack($level->getAttack());
                $old->setSkills($level->getSkills());
                $old->setLevel($level->getLevel());
            }else {
                $old = $level;
                $old->setCard($oldCard);
            }
            $levels->set($old->getTuoId(), $old);
        }
        return $levels;
    }
}
