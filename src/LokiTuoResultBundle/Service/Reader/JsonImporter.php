<?php


namespace LokiTuoResultBundle\Service\Reader;


use Exception;
use LokiTuoResultBundle\Entity\ResultFile;
use Psr\Log\LoggerAwareTrait;

class JsonImporter
{

    use LoggerAwareTrait;
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
                $content     = json_decode($file->getContent());
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

        return $count;
    }

    private function transformToModels($content)
    {
        $bge = $content->bge;
        $type = $content->type;
        $ordered = $content->ordered;
        $surge = $content->surge;
        foreach ($content->mission as $mission) {
            $player = null;
        }
    }
}