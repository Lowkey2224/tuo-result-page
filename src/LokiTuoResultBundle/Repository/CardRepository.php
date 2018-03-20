<?php

namespace App\LokiTuoResultBundle\Repository;

/**
 * CardRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CardRepository extends AbstractBaseRepository
{
    public function findByStartingLetter($letter, $orderBy = 'name', $orderDirection = 'ASC')
    {
        return $this->createQueryBuilder('c')
            ->where('c.name LIKE  :letter ')
            ->orderBy('c.' . $orderBy, $orderDirection)
            ->setParameter('letter', $letter . '%')
            ->getQuery()
            ->getResult();
    }

    public function findAllWithLevels()
    {
        $qb = $this->createQueryBuilder('c')
            ->addSelect('l')
            ->join('c.levels', 'l');
        return $qb->getQuery()->getResult();
    }

    public function findAllNames()
    {
        $qb = $this->createQueryBuilder('c')
            ->select("c.name");
        return $qb->getQuery()->getResult();
    }
}
