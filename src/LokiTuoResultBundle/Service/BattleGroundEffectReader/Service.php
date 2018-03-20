<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 05.12.16
 * Time: 20:22.
 */

namespace App\LokiTuoResultBundle\Service\BattleGroundEffectReader;

use App\LokiTuoResultBundle\Service\Persister\PersisterAwareTrait;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class Service
{
    use LoggerAwareTrait;
    use PersisterAwareTrait;

    /** @var EntityManager */
    private $em;

    public function __construct(EntityManager $em, $logger = null)
    {
        $logger = null === $logger ? new NullLogger() : $logger;
        $this->setLogger($logger);
        $this->em = $em;
    }

    /**
     * @param $filepath
     * @return int
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function readFile($filepath)
    {
        if (!file_exists($filepath)) {
            throw new FileNotFoundException('File with path ' . $filepath . ' could not be found');
        }
//        $persister = $this->getPersister();
        $content     = file_get_contents($filepath);
        $content     = explode("\n", $content);
        $transformer = new Transformer($this->logger);
        $models      = $transformer->transformToModels($content);
        $repo        = $this->em->getRepository('LokiTuoResultBundle:BattleGroundEffect');
        foreach ($models as $battleGroundEffect) {
            $old = $repo->findOneBy(['name' => $battleGroundEffect->getName()]);
            if ($old) {
                $old->setCategory($battleGroundEffect->getCategory());
                $old->setDescription($battleGroundEffect->getDescription());
                $this->em->persist($old);
            } else {
                $this->em->persist($battleGroundEffect);
            }
        }
        $this->em->flush();
//        return $persister->persistObjects($models, ['name']);
        return count($models);
    }
}
