<?php

namespace LokiTuoResultBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;

/**
 * Message.
 *
 * @ORM\Table(name="message")
 * @ORM\Entity(repositoryClass="LokiTuoResultBundle\Repository\MessageRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Message extends AbstractBaseEntity implements Translatable
{
    const STATUS_UNREAD = 1;
    const STATUS_READ = 2;
    /**
     * @var Player
     * @ORM\ManyToOne(targetEntity="Player", inversedBy="messages")
     * @ORM\JoinColumn(referencedColumnName="id", name="player_id")
     */
    private $player;


    /**
     * @var integer
     * @ORM\Column(type="integer")
     */
    private $status = self::STATUS_UNREAD;

    /**
     * @var string
     * @ORM\Column(type="string")
     * @Gedmo\Translatable
     */
    private $message;

    /**
     * @Gedmo\Locale
     * Used locale to override Translation listener`s locale
     * this is not a mapped field of entity metadata, just a simple property
     * and it is not necessary because globally locale can be set in listener
     */
    private $locale;

    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
        return $this;
    }

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

    public function serialize()
    {
        return [
            'id' => $this->getId(),
            'message' => $this->getMessage(),
            'read' => $this->isRead(),
            'player' => [
                'id' => $this->getPlayer()->getId(),
                'name' => $this->getPlayer()->getName(),
            ],
        ];
    }
}
