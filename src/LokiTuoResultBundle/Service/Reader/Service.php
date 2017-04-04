<?php

namespace LokiTuoResultBundle\Service\Reader;

use Doctrine\ORM\EntityManager;
use LokiTuoResultBundle\Entity\ResultFile;
use LokiTuoResultBundle\Service\OwnedCards\Service as CardManager;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 03.08.16
 * Time: 19:38.
 */
class Service
{
    use LoggerAwareTrait;

    //TODO Change the import so it can use different persisters
    //Maybe save the Import Simulation script too

    /** @var CardManager */
    private $ownedCardManager;

    /** @var EntityManager  */
    private $em;

    /**
     * Service constructor.
     *
     * @param EntityManager $entityManager
     * @param CardManager   $manager
     */
    public function __construct(EntityManager $entityManager, CardManager $manager, LoggerInterface $logger)
    {
        $this->em               = $entityManager;
        $this->ownedCardManager = $manager;
        $this->logger           = $logger;
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
        $jsonImporter = new JsonImporter($this->em, $this->ownedCardManager, $this->logger);
        $txtImporter = new TxtImporter($this->em, $this->ownedCardManager, $this->logger);

        foreach ($files as $file) {
            if ($file->getVersion() == 1) {
                $txtImporter->importFile($file, $count);
            } elseif ($file->getVersion() == 2) {
                $jsonImporter->importFile($file, $count);
            }
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
        if (strpos($name, ".txt")) {
            $file->setVersion(1);
        } elseif (strpos($name, ".json")) {
            $content = json_decode($file->getContent());
            $file->setVersion($content->version);
        } else {
            $file->setVersion(0);
        }
        return $file;
    }
}
