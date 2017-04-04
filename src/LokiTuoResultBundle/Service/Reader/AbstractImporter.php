<?php

namespace LokiTuoResultBundle\Service\Reader;

use Doctrine\ORM\EntityManager;
use Exception;
use LokiTuoResultBundle\Entity\DeckEntry;
use LokiTuoResultBundle\Entity\Result;
use LokiTuoResultBundle\Entity\ResultFile;
use Psr\Log\LoggerAwareTrait;
use LokiTuoResultBundle\Service\OwnedCards\Service as CardManager;
use Psr\Log\LoggerInterface;


abstract class AbstractImporter
{
    use LoggerAwareTrait;

    /** @var EntityManager */
    protected $em;

    /** @var CardManager */
    protected $ownedCardManager;

    /**
     * Service constructor.
     *
     * @param EntityManager $entityManager
     * @param CardManager   $manager
     */
    public function __construct(EntityManager $entityManager, CardManager $manager, LoggerInterface $logger)
    {
        $this->em               = $entityManager;
        $this->ownedCardManager = $manager;
        $this->logger           = $logger;
    }


    /**
     * Import a Resultfile so Missions & Results can be saved
     * @param ResultFile $file
     * @param integer $count
     * @return ResultFile
     */
    abstract public function importFile(ResultFile $file, &$count);

    /**
     * Parse a Resultline
     * @param $line
     * @return mixed
     */
    protected function parseResultLine($line)
    {
        if (preg_match('/member name (.*?)@/', $line, $name) === 1) {
            $name = $name[1];
            $result['playername'] = $name;
        }
        if (preg_match('/against (.*)/', $line, $name) === 1) {
            $name = $name[1];
            $result['mission'] = $name;
        }

        $result['simType'] = 'Mission';
        if (preg_match('/(\d?\d(.\d*)?):/', $line, $name) === 1) {
            $name = $name[1];
            $name = (int)($name * 10);
            $result['percent'] = $name;
        }
        if (preg_match('/\d?\d.?\d?\d?: (.*)/', $line, $name) === 1) {
            $name = $name[1];
            $cards = $this->transformToCardNames(explode(', ', $name));
            $result['deck'] = $cards;
        }
        if (preg_match('/(\d\d?% win)/', $line) === 1) {
            $result['simType'] = 'Raid';
        }

        return $result;
    }

    /**
     * Transform raw Cardstrings into correct Cardnames.
     *
     * @param array $array
     *
     * @return array
     */
    private function transformToCardNames(array $array)
    {
        $result = [];
        foreach ($array as $name) {
            if (preg_match('/([\w\h]+)/', $name, $res) === 1) {
                $result[] = trim($res[0]);
            }
        }

        return $result;
    }

    /**
     * Remove old Deck for previous Results.
     *
     * @param Result $result
     */
    protected function deleteOldDeck(Result $result)
    {
        foreach ($result->getDeck() as $deckItem) {
            $this->em->remove($deckItem);
        }
        $this->em->persist($result);
    }

    /**
     * Create a Deck from the result line.
     *
     * @param $deck
     * @param Result $result
     *
     * @throws Exception
     *
     * @return array
     */
    protected function createDeck($deck, Result $result)
    {
        $cardRepo   = $this->em->getRepository('LokiTuoResultBundle:Card');
        $resultDeck = [];
        $order      = 0;
        foreach ($deck as $cardName) {
            $_tmp   = $this->ownedCardManager->transformCardString($cardName);
            $amount = $_tmp['amount'];
            $level  = $_tmp['level'];
            $name   = $_tmp['name'];
            $card   = $cardRepo->findOneBy(['name' => $name]);

            if (! $card) {
                throw new Exception('No Card found with name:'.$name);
            }
            for ($i = 0; $i < $amount; ++$i) {
                $deckEntry = new DeckEntry();
                $deckEntry->setPlayOrder($order);
                $deckEntry->setLevel($level);
                $deckEntry->setResult($result);
                $deckEntry->setCard($card);
                $this->em->persist($deckEntry);
                ++$order;
                $resultDeck[] = $deckEntry;
            }
        }

        return $resultDeck;
    }
}
