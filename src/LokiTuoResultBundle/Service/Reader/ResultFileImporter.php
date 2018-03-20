<?php

namespace App\LokiTuoResultBundle\Service\Reader;

use App\LokiTuoResultBundle\Entity\ResultFile;
use Doctrine\ORM\EntityManager;
use Exception;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

class ResultFileImporter
{
    use LoggerAwareTrait;

    /** @var EntityManager */
    protected $em;

    /**
     * Service constructor.
     *
     * @param EntityManager   $entityManager
     * @param LoggerInterface $logger
     */
    public function __construct(EntityManager $entityManager, LoggerInterface $logger)
    {
        $this->em               = $entityManager;
        $this->logger           = $logger;
    }

    /**
     * Read a Resultfile, and save it to DB.
     *
     * @param $path
     *
     * @return int
     */
    public function readFile($path, $realname = null)
    {
        $this->logger->info("Reading file $path");
        $content = $this->getFileContents($path);

        $file = new ResultFile();
        if (!$realname) {
            $name = explode('/', $path);
            $realname = end($name);
        }

        $file->setOriginalName($realname);

        $file->setContent($content);
        $file = $this->setVersion($file);
        $file->setGuild($this->getGuildName($file));

        $this->em->persist($file);
        $this->em->flush();
        $this->logger->info('Persisting file with Id '.$file->getId());

        return $file->getId();
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
     * Return the Guildname for the given ResultFile.
     *
     * @param ResultFile $file
     *
     * @throws Exception
     *
     * @return mixed|string
     */
    private function getGuildName(ResultFile $file)
    {
        switch ($file->getVersion()) {
            case 1:
                return $this->getGuildNameV1($file);
            case 2:
                return $this->getGuildNameV2($file);
            default:
                throw new Exception('Unknown File Version: '.$file->getVersion());
        }
    }

    /**
     * Get Guild for File version 1
     * @param ResultFile $file
     * @return mixed
     * @throws Exception
     */
    private function getGuildNameV1(ResultFile $file)
    {
        $content = explode("\n", $file->getContent());
        $guild   = [];
        if (preg_match('/([a-zA-z]+) Results/', $content[0], $guild) === 1) {
            return $guild[1];
        } else {
            $this->logger->error(' NO Guild found in line: '.$content[0]);
            throw new Exception('No Guild Found');
        }
    }

    /**
     * Get Guildnames for Resultfile Version 2
     * @param ResultFile $file
     * @return string
     */
    private function getGuildNameV2(ResultFile $file)
    {
        $content     = json_decode($file->getContent());
        $guilds      = [];
        foreach ($content->missions as $mission) {
            foreach ($mission->results as $result) {
                $guilds[$result->guild] = $result->guild;
            }
        }

        return implode(', ', $guilds);
    }

    /**
     * Analyze the File and set the Version
     * @param ResultFile $file
     * @return ResultFile
     */
    private function setVersion(ResultFile $file)
    {
        $name = $file->getOriginalName();
        $this->logger->error($name. " checking version");
        if (strpos($name, '.txt')) {
            $file->setVersion(1);
        } elseif (strpos($name, '.json')) {
            $content = json_decode($file->getContent());
            $file->setVersion($content->version);
        } else {
            $file->setVersion(0);
        }

        return $file;
    }
}
