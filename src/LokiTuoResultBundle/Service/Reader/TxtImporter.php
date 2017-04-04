<?php


namespace LokiTuoResultBundle\Service\Reader;


use Exception;
use LokiTuoResultBundle\Entity\Mission;
use LokiTuoResultBundle\Entity\Player;
use LokiTuoResultBundle\Entity\Result;
use LokiTuoResultBundle\Entity\ResultFile;

class TxtImporter extends AbstractImporter
{

    /**
     * @inheritdoc
     */
    public function importFile(ResultFile $file, &$count)
    {
        try {
            $this->logger->info('Using File with ID '.$file->getId().' for Import');
            $content     = explode("\n", $file->getContent());
            $transformed = $this->transformContent($content);
            $this->logger->info('Importing Result for Guild '.$file->getGuild());
            $models = $this->transformToModels($transformed['result'], $file,$file->getGuild());
            $this->logger->info(count($models).' were Saved');
            $count += count($models);
            $file->setStatus(ResultFile::STATUS_IMPORTED);
            $file->setMissions(implode(', ', $transformed['missions']));
            $this->em->persist($file);
        } catch (Exception $ex) {
            $file->setStatus(ResultFile::STATUS_ERROR);
            throw  $ex;
        }
        return $file;
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
        $result = ['missions' => []];
        //THrow away first line
        array_shift($content);

        foreach ($content as $line) {
            $tmp = $this->parseResultLine($line);
            if (array_key_exists('mission', $tmp)) {
                $name = $tmp['mission'];
                $result['missions'][$name] = $name;
                $result['result'][$count] = $tmp;
            } else {
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
}