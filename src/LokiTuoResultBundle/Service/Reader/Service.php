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
 * Time: 19:38.
 */
class Service extends AbstractImporter
{
    use LoggerAwareTrait;

    //TODO Change the import so it can use different persisters
    //Maybe save the Import Simulation script too
    /** @var EntityManager */
    private $em;

    /** @var CardManager */
    private $ownedCardManager;

    /**
     * Service constructor.
     *
     * @param EntityManager $entityManager
     * @param CardManager   $manager
     */
    public function __construct(EntityManager $entityManager, CardManager $manager)
    {
        $this->em               = $entityManager;
        $this->ownedCardManager = $manager;
        $this->logger           = new NullLogger();
    }

    /**
     * Read a Resultfile, and save it to DB.
     *
     * @param $path
     *
     * @return int
     */
    public function readFile($path)
    {
        $this->logger->info("Reading file $path");
        $content = $this->getFileContents($path);

        $file = new ResultFile();
        $name = explode("/", $path);
        $file->setOriginalName(end($name));


        $file->setContent($content);
        $file->setGuild($this->getGuildName(explode("\n", $content)));
        $file = $this->setVersion($file);

        $this->em->persist($file);
        $this->em->flush();
        $this->logger->info('Persisting file with Id '.$file->getId());

        return $file->getId();
    }

    /**
     * Import a Resultfile which is saved into the DB and create results and missions.
     *
     * @param $fileId
     *
     * @return int
     */
    public function importFileById($fileId)
    {
        $files = $this->getFileById($fileId);
        if (empty($files)) {
            $this->logger->alert("No File with ID $fileId was found. Aborting");

            return 0;
        }
        $count = 0;

        foreach ($files as $file) {
            try {
                $this->logger->info('Using File with ID '.$file->getId().' for Import');
                $content     = explode("\n", $file->getContent());
                $transformed = $this->transformContent($content);
                $this->logger->info('Importing Result for Guild '.$transformed['guild']);
                $models = $this->transformToModels($transformed['result'], $file, $transformed['guild']);
                $this->logger->info(count($models).' were Saved');
                $count += count($models);
                $file->setGuild($transformed['guild']);
                $file->setStatus(ResultFile::STATUS_IMPORTED);
                $file->setMissions(implode(', ', $transformed['missions']));
            } catch (Exception $ex) {
                $file->setStatus(ResultFile::STATUS_ERROR);
            }
            $this->em->persist($file);
        }
        $this->em->flush();

        return $count;
    }

    /**
     * Reads the File content.
     *
     * @param $path
     *
     * @return string
     */
    private function getFileContents($path)
    {
        //Maybe do some validation here.
        return file_get_contents($path);
    }

    /**
     * Transform filecontent into an Array.
     *
     * @param $content
     *
     * @return array ['guild' => GuildName, 'result' => Results]
     */
    private function transformContent($content)
    {
        $count  = 0;
        $result = ['guild' => $this->getGuildName($content), 'missions' => []];
        //THrow away first line
        array_shift($content);

        foreach ($content as $line) {
            $tmp = $this->parseResultLine($line);
            if(array_key_exists('mission', $tmp)) {
                $name = $tmp['mission'];
                $result['missions'][$name] = $name;
                $result['result'][$count] = $tmp;
            }else {
                $result['result'][$count] = array_merge($result['result'][$count], $tmp);
                $count++;
            }

        }

        return $result;
    }

    /**
     * Transform an Array from transformContent into Models.
     *
     * @param $transformed
     * @param ResultFile $file
     * @param string     $guild
     *
     * @return Result[]
     */
    private function transformToModels($transformed, ResultFile $file, $guild)
    {
        $results     = [];
        $playerRepo  = $this->em->getRepository('LokiTuoResultBundle:Player');
        $missionRepo = $this->em->getRepository('LokiTuoResultBundle:Mission');
        $resultRepo  = $this->em->getRepository('LokiTuoResultBundle:Result');
        $guildRepo   = $this->em->getRepository('LokiTuoResultBundle:Guild');
        $guild       = $guildRepo->findOneBy(['name' => $guild]);
        foreach ($transformed as $line) {
            if (! isset($line['deck'])) {
                $this->logger->warning('Skipped result for Player '.
                    $line['playername'].' against '.$line['mission'].'. Because no Deck was found');
                continue;
            }

            if (! ($player = $playerRepo->findOneBy(['name' => $line['playername']]))) {
                $player = new Player();
                $player->setName($line['playername']);
                $this->em->persist($player);
            }
            if (! ($mission = $missionRepo->findOneBy(['name' => $line['mission']]))) {
                $mission = new Mission();
                $mission->setName($line['mission']);
            }
            $mission->setType($line['simType']);
            $mission->setUpdatedAtValue();
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
            $player->setGuild($guild);
            $this->em->persist($player);
            $this->em->persist($result);
            $this->deleteOldDeck($result);
            $deck = $this->createDeck($line['deck'], $result);
            $result->setDeck($deck);
            $this->em->persist($result);
            $results[] = $result;
            $this->logger->debug('Saving Result for Player '.$player->getName());
        }

        $this->em->flush();

        return $results;
    }

    /**
     * Create a Deck from the result line.
     *
     * @param $deck
     * @param Result $result
     *
     * @return DeckEntry[]
     */
    private function createDeck($deck, Result $result)
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
                $card = new Card();
                $card->setName($name);
                $this->em->persist($card);
                $this->em->flush();
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

    /**
     * Remove old Deck for previous Results.
     *
     * @param Result $result
     */
    private function deleteOldDeck(Result $result)
    {
        foreach ($result->getDeck() as $deckItem) {
            $this->em->remove($deckItem);
        }
        $this->em->flush();
    }

    /**
     * Get Resultfiles by ID.
     *
     * @param int|string $fileId Id || "next" || "all"
     *
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

    /**
     * Return the Guildname for the given ResultFile.
     *
     * @param $content
     *
     * @return string
     */
    private function getGuildName($content)
    {
        $guild = [];
        if (preg_match('/([a-zA-z]+) Results/', $content[0], $guild) === 1) {
            //Special case where CNS had the Name CTF seems legacy now
            //FIXME
            return ($guild[1] == 'CTF') ? 'CNS' : $guild[1];
        } else {
            $this->logger->error(' NO Guild found in line: '.$content[0]);
            throw new Exception('No Guild Found');
        }
    }


    private function setVersion(ResultFile $file)
    {
        $name = $file->getOriginalName();
        if(strpos($name, ".txt")) {
            $file->setVersion(1);
        }elseif(strpos($name, ".json")) {
            $content = json_decode($file->getContent());
            $file->setVersion($content->version);
        } else {
            $file->setVersion(0);
        }
        return $file;
    }
}
