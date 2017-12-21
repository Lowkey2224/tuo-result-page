<?php

namespace LokiTuoResultBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class BattleLog
 * @package LokiTuoResultBundle\Entity
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="LokiTuoResultBundle\Repository\BattleLogRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class BattleLog extends AbstractBaseEntity
{
    const STATUS_UNREAD = 1;
    const STATUS_READ = 2;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $battles;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $won;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $gold;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $rating;

    /**
     * @var Player
     * @ORM\ManyToOne(targetEntity="Player", inversedBy="battleLogs")
     * @ORM\JoinColumn(referencedColumnName="id", name="player_id")
     */
    private $player;

    /**
     * @var integer
     * @ORM\Column(type="integer")
     */
    private $status = self::STATUS_UNREAD;


    public function setStatusUnread()
    {
        $this->status = 1;
    }

    public function setStatusRead()
    {
        $this->status = 2;
    }

    public function isUnread()
    {
        return $this->status == self::STATUS_UNREAD;
    }

    public function isRead()
    {
        return $this->status == self::STATUS_READ;
    }

    /**
     * @return int
     */
    public function getBattles(): int
    {
        return $this->battles;
    }

    /**
     * @param int $battles
     * @return BattleLog
     */
    public function setBattles(int $battles)
    {
        $this->battles = $battles;
        return $this;
    }

    /**
     * @return int
     */
    public function getWon(): int
    {
        return $this->won;
    }

    /**
     * @param int $won
     * @return BattleLog
     */
    public function setWon(int $won)
    {
        $this->won = $won;
        return $this;
    }

    /**
     * @return int
     */
    public function getGold(): int
    {
        return $this->gold;
    }

    /**
     * @param int $gold
     * @return BattleLog
     */
    public function setGold(int $gold)
    {
        $this->gold = $gold;
        return $this;
    }

    /**
     * @return int
     */
    public function getRating(): int
    {
        return $this->rating;
    }

    /**
     * @param int $rating
     * @return BattleLog
     */
    public function setRating(int $rating)
    {
        $this->rating = $rating;
        return $this;
    }

    /**
     * @return Player
     */
    public function getPlayer(): Player
    {
        return $this->player;
    }

    /**
     * @param Player $player
     * @return BattleLog
     */
    public function setPlayer(Player $player)
    {
        $this->player = $player;
        return $this;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     * @return BattleLog
     */
    public function setStatus(int $status)
    {
        $this->status = $status;
        return $this;
    }


}
