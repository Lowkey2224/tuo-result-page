<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 09.08.16
 * Time: 20:45
 */

namespace LokiTuoResultBundle\Service\CardReader;

use Doctrine\ORM\EntityManager;
use LokiTuoResultBundle\Entity\CardFile;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Service
{
    /** @var EntityManager  */
    private $em;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
        $this->logger = new NullLogger();
    }

    public function saveCardFiles(array $fileNames)
    {
        foreach ($fileNames as $fileName) {
            //TODO Verify File exists
            $content = file_get_contents($fileName);
            $cardFile = new CardFile();
            $cardFile->setContent($content);
            $cardFile->setOriginalFileName($fileName);
            $this->em->persist($cardFile);
        }
        $this->em->flush();
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}