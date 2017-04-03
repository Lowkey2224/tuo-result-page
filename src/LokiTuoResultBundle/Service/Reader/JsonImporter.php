<?php

namespace LokiTuoResultBundle\Service\Reader;

use Doctrine\ORM\EntityManager;
use Exception;
use Illuminate\Support\Collection;
use LokiTuoResultBundle\Entity\Guild;
use LokiTuoResultBundle\Entity\Mission;
use LokiTuoResultBundle\Entity\Player;
use LokiTuoResultBundle\Entity\Result;
use LokiTuoResultBundle\Entity\ResultFile;
use Psr\Log\LoggerAwareTrait;

class JsonImporter extends AbstractImporter
{
    use LoggerAwareTrait;

    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Import a Resultfile which is saved into the DB and create results and missions.
     *
     * @param $file
     *
     * @return int
     */
    public function importFileById(ResultFile $file)
    {
        $count = 0;

        try {
            $this->logger->info('Using File with ID '.$file->getId().' for Import');
            list($missions, $guilds, $players) = $this->preFetchModels($file);
            $models     = $this->transformToModels($file, $missions, $players, $guilds);
            $this->logger->info(count($models).' were Saved');
            $count += count($models);

            $file->setStatus(ResultFile::STATUS_IMPORTED);
        } catch (Exception $ex) {
            $file->setStatus(ResultFile::STATUS_ERROR);
        }
        $this->em->persist($file);

        return $count;
    }

    /**
     * @param ResultFile $resultFile
     * @param Collection|Mission[] $missions
     * @param Collection|Player[] $players
     * @param Collection|Guild[] $guilds
     * @return Collection
     */
    private function transformToModels(ResultFile $resultFile, $missions, $players, $guilds)
    {
        $content     = json_decode($resultFile->getContent());
        $resultRepo  = $this->em->getRepository('LokiTuoResultBundle:Result');
        $result      = new Collection();
        $bge         = $content->bge;
        $simType     = $content->type;
        $missionType = $content->missionType;
        $ordered     = $content->ordered;
        $surge       = $content->surge;
        foreach ($content->mission as $mission) {

            $uuid = Mission::createUuid($mission->name, $bge, $mission->myStructures, new Mission());
            $missionEntity = $missions->get($uuid);
            $missionEntity->setName($mission->name);
            $missionEntity->setType($missionType);
            $missionEntity->setBge($bge);
            foreach ($mission->results as $result) {
                list($deck, $percent) = $this->parseResultLine($result->result);
                $player = $players->get($result->player_id);

                $resultEntity = $resultRepo->findOneBy(['player' => $player, 'mission' => $mission]);
                if (is_null($resultEntity)) {
                    $resultEntity = new Result();
                }

                $resultEntity->setGuild($guilds->get($result->guild_id));
                $resultEntity->setMission($missionEntity);
                $resultEntity->setDeck($deck);
                $resultEntity->setPercent($percent);
                $resultEntity->setSourceFile($resultFile);
                $resultEntity->setPlayer($player);
            }
        }

        return $result;
    }

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
                $ids['guilds'][$result->guild_id]  = $result->guild_id;
            }
        }
        $players  = new Collection($playerRepo->findByIds($ids['player']));
        $players  = $players->keyBy('id');
        $guilds   = new Collection($guildRepo->findByIds($ids['guild']));
        $guilds   = $guilds->keyBy('id');
        $missions = new Collection($missionRepo->finyByUuids($ids['mission']));
        $missions = $missions->keyBy('uuid');

        return [$missions, $guilds, $players];
    }
}
