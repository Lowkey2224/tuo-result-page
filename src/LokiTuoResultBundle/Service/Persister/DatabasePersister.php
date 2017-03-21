<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 05.12.16
 * Time: 20:44
 */

namespace LokiTuoResultBundle\Service\Persister;

use Doctrine\ORM\EntityManager;
use LokiTuoResultBundle\Entity\AbstractBaseEntity;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

class DatabasePersister implements PersisterInterface
{
    use LoggerAwareTrait;

    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->logger = new NullLogger();
    }

    /** @inheritdoc */
    public function persistObjects(array $entities, array $identifiers = ['id'])
    {
        $count = 0;
        /** @var AbstractBaseEntity $entity */
        foreach ($entities as $entity) {
            $found = $this->findOne($entity, $identifiers);
            if ($found) {
                $entity->setId($found->getId());
            } else {
                $msg = sprintf("No Entity found for class %s with id %d", get_class($entity), $entity->getId());
                $this->logger->error($msg);
                die("Nicht gefunden");
            }

            $this->em->persist($entity);
            $count++;
        }
        $this->em->flush();
        return $count;
    }

    private function findOne(AbstractBaseEntity $entity, array $identifiers = ['id'])
    {
        $repo = $this->em->getRepository($entity->getClassName());

        $criteria = [];
        foreach ($identifiers as $identifier) {
            $getter = $this->getGetterName($identifier);
            $criteria[$identifier] = $entity->$getter();
        }
        return $repo->findOneBy($criteria);
    }

    /**
     * constructs the name of the getter function for the Given attribute
     * @param string $attribute e.g. id
     * @return string e.g. getId
     */
    private function getGetterName($attribute)
    {
        return "get" . strtoupper(substr($attribute, 0, 1)) . substr($attribute, 1);
    }
}
