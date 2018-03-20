<?php

namespace App\LokiTuoResultBundle\Repository;

use App\LokiTuoResultBundle\Entity\BattleLog;
use App\LokiTuoResultBundle\Entity\Player;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use LokiUserBundle\Entity\User;

class BattleLogRepository extends AbstractBaseRepository
{
    /**
     * Returns the total number of Messages for all Players a User Owns
     * @param User $user
     * @return int
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countForUser(User $user)
    {
        $qb = $this->createQueryBuilder('b')
            ->join('b.player', 'p')
            ->select("COUNT(b.id)")
            ->where("p.owner = :id")
            ->andWhere('b.status = :status')
            ->setParameter("id", $user->getId())
            ->setParameter("status", BattleLog::STATUS_UNREAD);
        $res = $qb->getQuery()->getSingleResult();
        return $res[1];
    }

    public function findUnreadByPlayer(Player $player)
    {
        $qb = $this->createQueryBuilder('b')
            ->join('b.player', 'p')
            ->where("p.id = :id")
            ->andWhere('b.status = :status')
            ->setParameter("id", $player->getId())
            ->setParameter("status", BattleLog::STATUS_UNREAD);
        return $qb->getQuery()->getResult();
    }

    public function getTotaldByPlayer(Player $player)
    {
        $qb = $this->createQueryBuilder('b')
            ->select("sum(b.battles) as battles")
            ->addSelect("sum(b.gold) as gold")
            ->addSelect("sum(b.rating)as rating")
            ->addSelect("sum(b.won) as won")
            ->join('b.player', 'p')
            ->where("p.id = :id")
            ->setParameter("id", $player->getId());
        try {
            return $qb->getQuery()->getSingleResult();
        } catch (NoResultException $e) {
            return null;
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }
}
