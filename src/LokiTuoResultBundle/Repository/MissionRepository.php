<?php

namespace LokiTuoResultBundle\Repository;

/**
 * MissionRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class MissionRepository extends \Doctrine\ORM\EntityRepository
{

    public function findMissionsForGuild($guild)
    {
        $qb = $this->createQueryBuilder('m')
            ->addSelect('r')
            ->join('m.results', 'r')
            ->where("r.guild  LIKE :guild")
            ->setParameter('guild', $guild)
            ->orderBy('m.name', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function findAllWithGuilds()
    {
        $qb = $this->createQueryBuilder('m')
            ->addSelect('r.guild')
            ->join('m.results', 'r')
            ->orderBy('m.name', 'ASC');
        return $qb->getQuery()->getResult();
    }

    public function findAllWithGuilds2()
    {
        $qb = $this->createQueryBuilder('m')
            ->addSelect('group_concat(r.guild)')
            ->join('m.results', 'r')
            ->orderBy('m.name', 'ASC')
            ->groupBy('m.id');

        return $qb->getQuery()->getResult();
    }
}
