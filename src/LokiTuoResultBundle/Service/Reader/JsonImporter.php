<?php

namespace LokiTuoResultBundle\Service\Reader;

use Doctrine\ORM\EntityManager;
use Exception;
use Illuminate\Support\Collection;
use LokiTuoResultBundle\Entity\DeckEntry;
use LokiTuoResultBundle\Entity\Guild;
use LokiTuoResultBundle\Entity\Mission;
use LokiTuoResultBundle\Entity\Player;
use LokiTuoResultBundle\Entity\Result;
use LokiTuoResultBundle\Entity\ResultFile;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use LokiTuoResultBundle\Service\OwnedCards\Service as CardManager;

class JsonImporter extends AbstractImporter
{
    use LoggerAwareTrait;

    /** @var CardManager  */
    private $ocManager;

    public function __construct(EntityManager $em, CardManager $manger)
    {
        $this->em = $em;
        $this->setLogger(new NullLogger());
        $this->ocManager = $manger;
    }

    /**
     * Import a Resultfile which is saved into the DB and create results and missions.
     *
     * @param ResultFile $file
     *
     * @return ResultFile
     */
    public function importFile(ResultFile $file, &$count)
    {


        try {
            $this->logger->info('Using File with ID '.$file->getId().' for Import');
            list($missions, $guilds, $players, $bge) = $this->preFetchModels($file);
            $models     = $this->transformToModels($file, $missions, $players, $guilds, $bge);
            $this->logger->info(count($models).' were Saved');
            $count += count($models);
            $this->em->persist($file);
            $file->setStatus(ResultFile::STATUS_IMPORTED);
        } catch (Exception $ex) {
            $file->setStatus(ResultFile::STATUS_ERROR);
            throw  $ex;
        }


        return $file;
    }

    /**
     * @param ResultFile $resultFile
     * @param Collection|Mission[] $missions
     * @param Collection|Player[] $players
     * @param Collection|Guild[] $guilds
     * @return Collection
     */
    private function transformToModels(ResultFile $resultFile, $missions, $players, $guilds, $bge)
    {
        $content     = json_decode($resultFile->getContent());

        $resultRepo  = $this->em->getRepository('LokiTuoResultBundle:Result');
        $results      = new Collection();

        $simType     = $content->type;
        //TODO ADD MissionType
        $missionType = "Mission";
        $ordered     = $content->ordered;
        $surge       = $content->surge;

        foreach ($content->missions as $mission) {
            $uuid = Mission::createUuid($mission->name, $bge, $mission->myStructures);

            /** @var Mission $missionEntity */
            $missionEntity = $missions->get($uuid, new Mission());
            $missionEntity->setName($mission->name);
            $missionEntity->setStructures($mission->myStructures);
            $missionEntity->setType($missionType);
            $missionEntity->setBge($bge);
            $this->em->persist($missionEntity);
            foreach ($mission->results as $result) {
                $parsed = $this->parseResultLine($result->result);

                $player = $players->get($result->player_id);

                $resultEntity = $resultRepo->findOneBy(['player' => $player, 'mission' => $missionEntity]);
                if (!$resultEntity) {
                    $resultEntity = new Result();
                }

                $resultEntity->setGuild($guilds->get($result->guild_id));
                $resultEntity->setMission($missionEntity);
                //TODO Create real Deck
                $this->deleteOldDeck($resultEntity);
                $deck = $this->createDeck($parsed['deck'], $resultEntity);
                $resultEntity->setDeck($deck);
//                $resultEntity->setDeck($parsed['deck']);
                $resultEntity->setPercent($parsed['percent']);
                $resultEntity->setSourceFile($resultFile);
                $resultEntity->setPlayer($player);
                $results->push($resultEntity);
            }
        }
        return $results;
    }

    /**
     * @param ResultFile $resultFile
     * @return Collection[]
     */
    private function preFetchModels(ResultFile $resultFile)
    {
        $content     = json_decode($resultFile->getContent());
        $missionRepo = $this->em->getRepository('LokiTuoResultBundle:Mission');
        $playerRepo  = $this->em->getRepository('LokiTuoResultBundle:Player');
        $guildRepo   = $this->em->getRepository('LokiTuoResultBundle:Guild');
        $ids         = ['mission' => [], 'player' => [], 'guild' => []];
        $bge         = $this->em->getRepository('LokiTuoResultBundle:BattleGroundEffect')
            ->findOneBy(['name' => $content->bge]);
        foreach ($content->missions as $mission) {
            $uuid             = Mission::createUuid($mission->name, $bge, $mission->myStructures);
            $ids['mission'][] = $uuid;
            foreach ($mission->results as $result) {
                $ids['player'][$result->player_id] = $result->player_id;
                $ids['guild'][$result->guild_id]  = $result->guild_id;
            }
        }
        $players  = new Collection($playerRepo->findByIds($ids['player']));


        $players  = $players->keyBy(function (Player $p) {
            return $p->getId();
        });
        $guilds   = new Collection($guildRepo->findByIds($ids['guild']));
        $guilds   = $guilds->keyBy(function (Guild $p) {
            return $p->getId();
        });
        $missions = new Collection($missionRepo->finyByUuids($ids['mission']));
        $missions = $missions->keyBy(function (Mission $p) {
            return $p->getUuid();
        });
        return [$missions, $guilds, $players, $bge];
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
            $_tmp   = $this->ocManager->transformCardString($cardName);
            $amount = $_tmp['amount'];
            $level  = $_tmp['level'];
            $name   = $_tmp['name'];
            $card   = $cardRepo->findOneBy(['name' => $name]);

            if (! $card) {
                throw new Exception("No Card found with name:". $name);
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
