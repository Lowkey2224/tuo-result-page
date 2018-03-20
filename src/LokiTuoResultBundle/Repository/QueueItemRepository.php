<?php

namespace App\LokiTuoResultBundle\Repository;

class QueueItemRepository extends AbstractBaseRepository
{
    public function findActive()
    {
        $qb = $this->createQueryBuilder('q')
            ->where("q.status = :status1")
            ->orWhere("q.status = :status2")
            ->orderBy("q.id", "ASC")
            ->setParameter("status1", "running")
            ->setParameter("status2", "waiting");
        return $qb->getQuery()->getResult();
    }
}
