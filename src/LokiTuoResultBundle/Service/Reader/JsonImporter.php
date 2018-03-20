<?php

namespace App\LokiTuoResultBundle\Service\Reader;

use App\LokiTuoResultBundle\Entity\BattleGroundEffect;
use App\LokiTuoResultBundle\Entity\Card;
use App\LokiTuoResultBundle\Entity\Guild;
use App\LokiTuoResultBundle\Entity\Mission;
use App\LokiTuoResultBundle\Entity\Player;
use App\LokiTuoResultBundle\Entity\Result;
use App\LokiTuoResultBundle\Entity\ResultFile;
use Illuminate\Support\Collection;

class JsonImporter extends AbstractImporter
{

    /**
     * @inheritdoc
     */
    public function importFile(ResultFile $file, &$count)
    {

        $this->logger->info('Using File with ID ' . $file->getId() . ' for Import');
        list($missions, $guilds, $players, $bge) = $this->preFetchModels($file);
        $cards = $this->getAllCards();
        $models = $this->transformToModels($file, $missions, $players, $guilds, $cards, $bge);
        $this->logger->info(count($models) . ' were Saved');
        $count += count($models);
        $this->em->persist($file);
        $file->setStatus(ResultFile::STATUS_IMPORTED);

        $file->setStatus(ResultFile::STATUS_ERROR);

        return $file;
    }

    /**
     * @param ResultFile $resultFile
     * @param Collection|Mission[] $missions
     * @param Collection|Player[] $players
     * @param Collection|Guild[] $guilds
     * @param Collection|Card[] $cards
     * @param mixed $bge
     *
     * @return Collection
     */
    private function transformToModels(
        ResultFile $resultFile,
        $missions,
        $players,
        $guilds,
        $cards,
        BattleGroundEffect $bge = null
    ) {
        $content = json_decode($resultFile->getContent());

        $resultRepo = $this->em->getRepository('LokiTuoResultBundle:Result');
        $results = new Collection();

        $simType = $content->type;
        //TODO ADD MissionType
        $missionType = 'Mission';
        $ordered = $content->ordered;
        $surge = $content->surge;

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
                if (!$result->result) {
                    $this->logger->warning(sprintf("Player %s has no result for mission with UUID %s", $result->player,
                        $uuid));
                    continue;
                }
                $parsed = $this->parseResultLine($result->result);

                $player = $players->get($result->player_id);

                $resultEntity = $resultRepo->findOneBy(['player' => $player, 'mission' => $missionEntity]);
                if (!$resultEntity) {
                    $resultEntity = new Result();
                }

                $resultEntity->setGuild($guilds->get($result->guild_id));
                $resultEntity->setMission($missionEntity);
                $resultEntity->setPlayer($player);
                $this->deleteOldDeck($resultEntity);
                $deck = $this->createDeck($parsed['deck'], $resultEntity, $cards);
                $resultEntity->setDeck($deck);
                $resultEntity->setPercent($parsed['percent']);
                $resultEntity->setSourceFile($resultFile);
                $results->push($resultEntity);
            }
        }

        return $results;
    }

    /**
     * @param ResultFile $resultFile
     *
     * @return Collection[]
     */
    private function preFetchModels(ResultFile $resultFile)
    {
        $content = json_decode($resultFile->getContent());
        $missionRepo = $this->em->getRepository('LokiTuoResultBundle:Mission');
        $playerRepo = $this->em->getRepository('LokiTuoResultBundle:Player');
        $guildRepo = $this->em->getRepository('LokiTuoResultBundle:Guild');
        $ids = ['mission' => [], 'player' => [], 'guild' => []];
        $bge = $this->em->getRepository('LokiTuoResultBundle:BattleGroundEffect')
            ->findOneBy(['name' => $content->bge]);
        foreach ($content->missions as $mission) {
            $uuid = Mission::createUuid($mission->name, $bge, $mission->myStructures);
            $ids['mission'][] = $uuid;
            foreach ($mission->results as $result) {
                $ids['player'][$result->player_id] = $result->player_id;
                $ids['guild'][$result->guild_id] = $result->guild_id;
            }
        }
        $players = new Collection($playerRepo->findByIds($ids['player']));

        $players = $players->keyBy(function (Player $p) {
            return $p->getId();
        });
        $guilds = new Collection($guildRepo->findByIds($ids['guild']));
        $guilds = $guilds->keyBy(function (Guild $p) {
            return $p->getId();
        });
        $missions = new Collection($missionRepo->finyByUuids($ids['mission']));
        $missions = $missions->keyBy(function (Mission $p) {
            return $p->getUuid();
        });

        return [$missions, $guilds, $players, $bge];
    }
}
