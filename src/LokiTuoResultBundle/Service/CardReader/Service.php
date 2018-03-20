<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 09.08.16
 * Time: 20:45.
 */

namespace App\LokiTuoResultBundle\Service\CardReader;

use App\LokiTuoResultBundle\Entity\CardFile;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

class Service
{
    /** @var EntityManager */
    private $em;

    use LoggerAwareTrait;

    public function __construct(EntityManager $entityManager)
    {
        $this->em     = $entityManager;
        $this->logger = new NullLogger();
    }

    public function saveCardFiles(array $fileNames)
    {
        $cfRepo = $this->em->getRepository('LokiTuoResultBundle:CardFile');
        $count  = 0;
        foreach ($fileNames as $fileName) {
            if (file_exists($fileName)) {
                $content  = file_get_contents($fileName);
                $cardFile = new CardFile();
                $cardFile->setContent($content);
                $cardFile->setOriginalFileName($fileName);
                if (!$cfRepo->findOneBy(['checksum' => $cardFile->getChecksum()])) {
                    $this->em->persist($cardFile);
                    $this->logger->debug('Read File: ' . $fileName);
                    ++$count;
                } else {
                    $this->logger->info('File ' . $fileName . ' exists already in Database');
                }
            } else {
                $this->logger->warning('File does not exist: ' . $fileName);
            }
        }
        $this->em->flush();

        return $count;
    }
}
