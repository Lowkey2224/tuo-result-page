<?php


namespace LokiTuoResultBundle\Repository;

use Doctrine\ORM\EntityRepository;

abstract class AbstractBaseRepository extends EntityRepository
{
    public function findByIds(array $ids)
    {
        return $this->createQueryBuilder('ae')
            ->where('ae.id in (:ids)')
            ->setParameter('ids', $ids)
        ->getQuery()->getResult();
    }
}
