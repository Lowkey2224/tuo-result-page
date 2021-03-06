<?php

namespace LokiTuoResultBundle\Service\Reader;

use Doctrine\ORM\EntityManager;
use Exception;
use LokiTuoResultBundle\Entity\ResultFile;
use LokiTuoResultBundle\Service\OwnedCards\Service as CardManager;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

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

    /** @var EntityManager */
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
    public function readFile($path, $realname = null)
    {
        $reader = new ResultFileImporter($this->em, $this->logger);
        return $reader->readFile($path, $realname);
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
        $count        = 0;
        $jsonImporter = new JsonImporter($this->em, $this->ownedCardManager, $this->logger);
        $txtImporter  = new TxtImporter($this->em, $this->ownedCardManager, $this->logger);

        foreach ($files as $file) {
            try {
                switch ($file->getVersion()) {
                    case 1:
                        $this->logger->info(sprintf("Importing file with id %d as text", $file->getId()));
                        $txtImporter->importFile($file, $count);
                        break;
                    case 2:
                        $this->logger->info(sprintf("Importing file with id %d as json", $file->getId()));
                        $jsonImporter->importFile($file, $count);
                        break;
                    default:
                        throw new Exception('Unknown Resultfile Version');
                }
            } catch (Exception $ex) {
                $this->logger->error($ex->getMessage());
                $this->logger->error($ex->getTraceAsString());
                $file->setStatus(ResultFile::STATUS_ERROR);
                $this->em->persist($file);
            }

        }
        $this->em->flush();

        return $count;
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
}
