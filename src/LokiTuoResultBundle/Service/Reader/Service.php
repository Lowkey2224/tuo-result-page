<?php

namespace LokiTuoResultBundle\Service\Reader;

use Doctrine\ORM\EntityManager;
use LokiTuoResultBundle\Entity\Card;
use LokiTuoResultBundle\Entity\DeckEntry;
use LokiTuoResultBundle\Entity\Mission;
use LokiTuoResultBundle\Entity\Player;
use LokiTuoResultBundle\Entity\Result;
use LokiTuoResultBundle\Entity\ResultFile;
use LokiTuoResultBundle\Service\OwnedCards\Service as CardManager;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 03.08.16
 * Time: 19:38
 */
class Service
{

    use LoggerAwareTrait;

    //TODO Change the import so it can use different persisters
    //Maybe save the Import Simulation script too
    /** @var  EntityManager */
    private $em;

    /** @var CardManager  */
    private $ownedCardManager;

    public function __construct(EntityManager $entityManager, CardManager $manager)
    {
        $this->em = $entityManager;
        $this->ownedCardManager = $manager;
        $this->logger = new NullLogger();
    }


    public function readFile($path)
    {
        $this->logger->info("Reading file $path");
        $content = $this->getFileContents($path);

        $file = new ResultFile();
        $file->setContent($content);
        $file->setGuild($this->getGuildName(explode("\n", $content)));
        $this->em->persist($file);
        $this->em->flush();
        $this->logger->info("Persisting file with Id " . $file->getId());
        return $file->getId();
    }

    public function importFileById($fileId)
    {
        $files = $this->getFileById($fileId);
        if (empty($files)) {
            $this->logger->alert("No File with ID $fileId was found. Aborting");
            return 0;
        }
        $count = 0;
        foreach ($files as $file) {
            $this->logger->info("Using File with ID " . $file->getId() . " for Import");
            $content = explode("\n", $file->getContent());
            $transformed = $this->transformContent($content);
            $this->logger->info("Importing Result for Guild " . $transformed['guild']);
            $models = $this->transformToModels($transformed['result'], $file, $transformed['guild']);
            $this->logger->info(count($models) . " were Saved");
            $count += count($models);
            $file->setStatus(ResultFile::STATUS_IMPORTED);
            $this->em->persist($file);
            $this->em->flush();
        }
        return $count;
    }

    private function getFileContents($path)
    {
        //Maybe do some validation here.
        return file_get_contents($path);
    }

    /**
     * @param $content
     * @return array ['guild' => GuildName, 'result' => Results]
     */
    private function transformContent($content)
    {

        $firstLine = true;
        $count = 0;
        $result = ['guild' => $this->getGuildName($content)];
        //THrow away first line
        array_shift($content);

        foreach ($content as $line) {
            if ($firstLine) {
                if (preg_match('/member name (.*?)@/', $line, $name) === 1) {
                    $name = $name[1];
                    $result['result'][$count]['playername'] = $name;
                }
                if (preg_match('/against (.*)/', $line, $name) === 1) {
                    $name = $name[1];
                    $result['result'][$count]['mission'] = $name;
                }
                $firstLine = false;
            } else {
                if (preg_match('/(\d?\d.?\d?\d?):/', $line, $name) === 1) {
                    $name = $name[1];
                    $name = (int)($name * 10);
                    $result['result'][$count]['percent'] = $name;
                }
                if (preg_match('/\d?\d.?\d?\d?: (.*)/', $line, $name) === 1) {
                    $name = $name[1];
                    $cards = $this->transformToCardNames(explode(", ", $name));
                    $result['result'][$count]['deck'] = $cards;
                }
                if (preg_match('/(\d\d?% win)/', $line) === 1) {
                    $result['result'][$count]['simType'] = 'Raid';
                }
                $firstLine = true;
                $count++;
            }
        }
        return $result;
    }

    private function transformToCardNames(array $array)
    {
        $result = [];
        foreach ($array as $name) {
            $tmp = explode("#", $name);
            if (count($tmp) == 2) {
                for ($i = 0; $i < $tmp[1]; $i++) {
                    $result[] = trim($tmp[0]);
                }
            } else {
                $result[] = trim($name);
            }
        }

        return $result;
    }

    private function transformToModels($transformed, ResultFile $file, $guild)
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
            }
            $mission->setType($line['simType']);
            $this->em->persist($mission);

            $result = $resultRepo->findOneBy(['player' => $player, 'mission' => $mission]);
            if (is_null($result)) {
                $result = new Result();
            }
            $result->setGuild($guild);
            $result->setSourceFile($file);
            $result->setPlayer($player);
            $result->setPercent($line['percent']);
            $result->setMission($mission);
            $player->setCurrentGuild($guild);
            $this->em->persist($player);
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
            $_tmp = $this->ownedCardManager->transformCardString($cardName);
            $amount = $_tmp['amount'];
            $level = $_tmp['level'];
            $name = $_tmp['name'];
            $card = $cardRepo->findOneBy(['name' => $name]);

            if (!$card) {
                $card = new Card();
                $card->setName($name);
                $this->em->persist($card);
                $this->em->flush();
            }
            for ($i = 0; $i < $amount; $i++) {
                $deckEntry = new DeckEntry();
                $deckEntry->setPlayOrder($order);
                $deckEntry->setLevel($level);
                $deckEntry->setResult($result);
                $deckEntry->setCard($card);
                $this->em->persist($deckEntry);
                $order++;
                $resultDeck[] = $deckEntry;
            }
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
     * @return ResultFile[]|null
     */
    private function getFileById($fileId)
    {
        $repo = $this->em->getRepository('LokiTuoResultBundle:ResultFile');
        if ($fileId === 'next') {
            return [$repo->findOneBy(['status' => ResultFile::STATUS_NOT_IMPORTED], ['id' => 'ASC'])];
        } elseif ($fileId === 'all') {
            return $repo->findBy(['status' => ResultFile::STATUS_NOT_IMPORTED], ['id' => 'ASC']);
        } else {
            return [$repo->find($fileId)];
        }
    }

    private function getGuildName($content)
    {
        $guild = [];
        if (preg_match('/([a-zA-z]+) Results/', $content[0], $guild) === 1) {
            return ($guild[1] == 'CTF') ? 'CNS' : $guild[1];
        } else {
            var_dump($guild, $content[0]);
            throw new Exception("No Guild Found");
        }
    }
}
