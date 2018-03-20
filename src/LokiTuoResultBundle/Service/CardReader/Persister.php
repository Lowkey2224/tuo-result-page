<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 10.08.16
 * Time: 13:15.
 */

namespace App\LokiTuoResultBundle\Service\CardReader;

use App\LokiTuoResultBundle\Entity\Card;
use App\LokiTuoResultBundle\Entity\CardFile;
use App\LokiTuoResultBundle\Entity\CardLevel;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Persister
{
    /** @var EntityManager */
    private $em;

    private $persist = true;

    /** @var Transformer */
    private $transformer;

    /**
     * Persister constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->logger = new NullLogger();
        $this->transformer = new Transformer();
        $this->transformer->setLogger($this->logger);
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->transformer->setLogger($logger);
    }

    public function importCards($force)
    {

        $criteria = $force === true ? [] : ['status' => CardFile::STATUS_NOT_IMPORTED];
        $files = $this->em->getRepository('LokiTuoResultBundle:CardFile')->findBy($criteria);
        $cardCount = 0;
        foreach ($files as $file) {
            $cardCount += $this->persistFile($file);
        }

        return $cardCount;
    }

    /**
     * Persists the content of this $file
     * @param CardFile $file
     * @return int Count of cards persisted.
     */
    private function persistFile(CardFile $file)
    {
        $this->logger->info(sprintf("Reading file %s ...", $file->getOriginalFileName()));
        $content = simplexml_load_string($file->getContent());
        $cards = $this->transformer->transformToModels($content, $file);
        $this->logger->info(sprintf("Finished reading file %s ...", $file->getOriginalFileName()));
        $cardCount = $this->persistModels($cards);
        $this->em->flush();
        $file->setStatus(CardFile::STATUS_IMPORTED);
        $this->em->persist($file);
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
        if (!$this->persist) {
            return $cards;
        }
        $cardRepo = $this->em->getRepository('LokiTuoResultBundle:Card');
        foreach ($cards as $key => $card) {
            /** @var Card $dbEntity */
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
            $old = $oldCard->getLevelByTuId($level->getTuoId());
            if ($old instanceof CardLevel) {
                $old->setDelay($level->getDelay());
                $old->setPicture($level->getPicture());
                $old->setDefense($level->getDefense());
                $old->setAttack($level->getAttack());
                $old->setSkills($level->getSkills());
                $old->setLevel($level->getLevel());
            } else {
                $old = $level;
                $old->setCard($oldCard);
            }
            $levels->set($old->getTuoId(), $old);
        }
        return $levels;
    }
}
