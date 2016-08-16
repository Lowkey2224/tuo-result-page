<?php

namespace LokiTuoResultBundle\Service\Reader;

use Doctrine\ORM\EntityManager;
use LokiTuoResultBundle\Entity\Card;
use LokiTuoResultBundle\Entity\Deck;
use LokiTuoResultBundle\Entity\Mission;
use LokiTuoResultBundle\Entity\Player;
use LokiTuoResultBundle\Entity\Result;
use LokiTuoResultBundle\Entity\ResultFile;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 03.08.16
 * Time: 19:38
 */
class Service
{

    //TODO Change the import so it can use different persisters
    //Maybe save the Import Simulation script too
    /** @var  EntityManager */
    private $em;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
        $this->logger = new NullLogger();
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function readFile($path)
    {
        $this->logger->info("Reading file $path");
        $content = $this->getFileContents($path);
        $file = new ResultFile();
        $file->setContent($content);
        $this->em->persist($file);
        $this->em->flush();
        $this->logger->info("Persisting file with Id " . $file->getId());
        return $file->getId();
    }

    public function importFileById($fileId)
    {
        $file = $this->getFileById($fileId);
        if (is_null($file)) {
            $this->logger->alert("No File with ID $fileId was found. Aborting");
            return 0;
        }
        $this->logger->info("Using File with ID " . $file->getId() . " for Import");

        $content = explode("\n", $file->getContent());
        $transformed = $this->transformContent($content);
        $models = $this->transformToModels($transformed, $file);
        $this->logger->info(count($models) . " were Saved");
        $file->setStatus(ResultFile::STATUS_IMPORTED);
        $this->em->persist($file);
        $this->em->flush();
        return count($models);
    }

    private function getFileContents($path)
    {
        //Maybe do some validation here.
        return file_get_contents($path);
    }

    private function transformContent($content)
    {
        $result = [];
        $firstLine = true;
        $count = 0;
        //THrow away first line
        array_shift($content);
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
                if (preg_match('/units: (\d?\d.?\d?\d?):/', $line, $name) === 1) {
                    $name = $name[1];
                    $name = (int)($name * 10);
                    $result[$count]['percent'] = $name;
                }
                if (preg_match('/units: \d?\d.?\d?\d?: (.*)/', $line, $name) === 1) {
                    $name = $name[1];
                    $result[$count]['deck'] = explode(", ", $name);
                }
                $firstLine = true;
                $count++;
            }
        }
        return $result;
    }

    private function transformToModels($transformed, ResultFile $file)
    {
        $results = [];
        $playerRepo = $this->em->getRepository('LokiTuoResultBundle:Player');
        $missionRepo = $this->em->getRepository('LokiTuoResultBundle:Mission');
        $resultRepo = $this->em->getRepository('LokiTuoResultBundle:Result');
        foreach ($transformed as $line) {
            if (!isset($line['deck'])) {
                $this->logger->warning("Skipped result for Player " .
                    $line['playername'] . " against " . $line['mission'] . ". Because no Deck was found");
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
            if (is_null($result)) {
                $result = new Result();
            }
            $result->setSourceFile($file);
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
        foreach ($result->getDeck() as $deckItem) {
            $this->em->remove($deckItem);
        }
        $this->em->flush();
    }

    /**
     * @param $fileId
     * @return ResultFile|null
     */
    private function getFileById($fileId)
    {
        $repo = $this->em->getRepository('LokiTuoResultBundle:ResultFile');
        if ($fileId === 'next') {
            return $repo->findOneBy(['status' => ResultFile::STATUS_NOT_IMPORTED], ['id' => 'ASC']);
        } else {
            return $repo->find($fileId);
        }
    }
}
