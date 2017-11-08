<?php

namespace LokiTuoResultBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Message.
 *
 * @ORM\Table(name="message")
 * @ORM\Entity(repositoryClass="LokiTuoResultBundle\Repository\MessageRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Message extends AbstractBaseEntity
{
    /**
     * @var Player
     * @ORM\ManyToOne(targetEntity="Player", inversedBy="ownedCards")
     * @ORM\JoinColumn(referencedColumnName="id", name="player_id")
     */
    private $player;


    /**
     * @var integer
     * @ORM\Column(type="integer")
     */
    private $status;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $message;

    /**
     * @return Player
     */
    public function getPlayer(): Player
    {
        return $this->player;
    }

    /**
     * @param Player $player
     * @return Message
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
     * @return Message
     */
    public function setStatus(int $status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     * @return Message
     */
    public function setMessage(string $message)
    {
        $this->message = $message;
        return $this;
    }

}
