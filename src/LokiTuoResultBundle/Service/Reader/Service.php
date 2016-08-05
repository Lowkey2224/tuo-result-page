<?php

namespace LokiTuoResultBundle\Service\Reader;

use Doctrine\ORM\EntityManager;
use LokiTuoResultBundle\Entity\Card;
use LokiTuoResultBundle\Entity\Deck;
use LokiTuoResultBundle\Entity\Mission;
use LokiTuoResultBundle\Entity\Player;
use LokiTuoResultBundle\Entity\Result;


/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 03.08.16
 * Time: 19:38
 */
class Service
{
    /** @var  EntityManager */
    private $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    public function readFile($path)
    {
        $content = $this->getFileContents($path);
        $transformed = $this->transformContent($content);
        $models = $this->transformToModels($transformed);
        return count($models);

    }

    private function getFileContents($path)
    {
        $content = [];
        $handle = fopen($path, "r");
        if ($handle) {
            fgets($handle); //Throw away first line.
            while (($line = fgets($handle)) !== false) {
                $content[] = $line;
            }

            fclose($handle);
        } else {
            // error opening the file.
        }
        return $content;
    }

    private function transformContent($content)
    {
        $result = [];
        $firstLine = true;
        $count = 0;
        foreach ($content as $line) {
            if ($firstLine) {
                if (preg_match('/member name (.*?)@/', $line, $name) === 1) {
                    $name = $name[1];
                    $result[$count]['playername'] = $name;
                }
                if (preg_match('/against (.*)/', $line, $name) === 1) {
                    $name = $name[1];
                    $result[$count]['mission'] = $name;
                }
                $firstLine = false;
            } else {
                if (preg_match('/units: (\d\d.?\d?\d?):/', $line, $name) === 1) {
                    $name = $name[1];
                    $name = (int)($name*10);
                    $result[$count]['percent'] = $name;
                }
                if (preg_match('/units: \d\d.?\d?\d?: (.*)/', $line, $name) === 1) {
                    $name = $name[1];
                    $result[$count]['deck'] = explode(", ", $name);
                }
                $firstLine = true;
                $count++;

            }
        }
        return $result;
    }

    private function transformToModels($transformed)
    {
        $results = [];
        $playerRepo = $this->em->getRepository('LokiTuoResultBundle:Player');
        $missionRepo = $this->em->getRepository('LokiTuoResultBundle:Mission');
        $resultRepo = $this->em->getRepository('LokiTuoResultBundle:Result');
        foreach ($transformed as $line) {
            if (!isset($line['deck']))
            {
                echo "\n Skipped result for Player ". $line['playername']. " Because no Deck was found\n";
                continue;
            }


            if (!($player = $playerRepo->findOneBy(['name' => $line['playername']]))) {
                $player = new Player();
                $player->setName($line['playername']);
                $this->em->persist($player);
            }
            if (!($mission = $missionRepo->findOneBy(['name' => $line['mission']]))) {
                $mission = new Mission();
                $mission->setName($line['mission']);
                $mission->setType("Mission");
                $this->em->persist($mission);
            }
            $result = $resultRepo->findOneBy(['player' => $player, 'mission' => $mission]);
            $result= ($results)?$result:new Result();
            $result->setPlayer($player);
            $result->setPercent($line['percent']);
            $result->setMission($mission);
            $this->em->persist($result);
            $this->deleteOldDeck($result);
            $deck = $this->createDeck($line['deck'], $result);
            $result->setDeck($deck);
            $this->em->persist($result);
            $results[] = $result;
        }

        $this->em->flush();
        return $results;
    }

    private function createDeck($deck, Result $result)
    {
        $cardRepo = $this->em->getRepository('LokiTuoResultBundle:Card');
        $resultDeck = [];
        $order = 0;
        foreach ($deck as $cardName) {
            $card = $cardRepo->findOneBy(['name' => $cardName]);

            if (!$card) {
                $card = new Card();
                $card->setName($cardName);
                $this->em->persist($card);
                $this->em->flush();
            }
            $deckEntry = new Deck();
            $deckEntry->setPlayOrder($order);
            $deckEntry->setResult($result);
            $deckEntry->setCard($card);
            $this->em->persist($deckEntry);
            $order++;
            $resultDeck[] = $deckEntry;
        }
        return $resultDeck;
    }

    private function deleteOldDeck(Result $result)
    {
        foreach ($result->getDeck() as $deckItem)
        {
            $this->em->remove($deckItem);
        }
        $this->em->flush();
    }
}