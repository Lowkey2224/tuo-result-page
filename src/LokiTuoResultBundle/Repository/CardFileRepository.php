<?php

namespace LokiTuoResultBundle\Repository;

/**
 * CardFileRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CardFileRepository extends AbstractBaseRepository
{
    public function getAllIds()
    {
        return $this->createQueryBuilder('cf')
            ->select('cf.id')
            ->orderBy('cf.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
