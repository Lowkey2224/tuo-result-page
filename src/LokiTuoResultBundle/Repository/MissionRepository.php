<?php

namespace LokiTuoResultBundle\Repository;

/**
 * MissionRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class MissionRepository extends AbstractBaseRepository
{
    /**
     * Search Missions according to Guilds.
     *
     * @param $guild
     *
     * @return array
     */
    public function findMissionsForGuild($guild)
    {
        $qb = $this->createQueryBuilder('m')
            ->addSelect('r')
            ->join('m.results', 'r')
            ->where('r.guild  LIKE :guild')
            ->setParameter('guild', $guild)
            ->orderBy('m.name', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Find All Missions with their concatenated Guilds.
     *
     * @return array [ [0=>mission, 1=>guilds], [....] ]
     */
    public function findAllWithGuilds2()
    {
        $qb = $this->createQueryBuilder('m')
            ->addSelect('group_concat(g.name)')
            ->join('m.results', 'r')
            ->join('r.guild', 'g')
            ->orderBy('m.name', 'ASC')
            ->groupBy('m.id');

        return $qb->getQuery()->getResult();
    }

    public function finyByUuids(array $uuids)
    {
        return $this->createQueryBuilder('mission')
            ->where('mission.uuid in (:uuids)')
            ->setParameter('uuids', $uuids)
            ->getQuery()->getResult();
    }
}
