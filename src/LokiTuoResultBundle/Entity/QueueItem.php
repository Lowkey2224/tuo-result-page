<?php

namespace App\LokiTuoResultBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use LokiUserBundle\Entity\User;

/**
 * Class QueueItem
 * @package App\LokiTuoResultBundle\Entity
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="App\LokiTuoResultBundle\Repository\QueueItemRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class QueueItem extends AbstractBaseEntity
{

    const STATUS_WAITING = "waiting";
    const STATUS_RUNNING = "running";
    const STATUS_FINISHED = "finished";
    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="LokiUserBundle\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="id", name="user_id", nullable=false)
     */
    protected $updatedBy;

    /**
     * @var string status
     * @ORM\Column(type="string", nullable=false)
     */
    protected $status;

    /**
     * @var string message
     * @ORM\Column(type="string", nullable=true)
     */
    protected $message;

    /**
     * QueueItem constructor.
     */
    public function __construct()
    {
        $this->setStatusWaiting();
    }

    /**
     * @return $this
     */
    public function setStatusWaiting()
    {
        $this->status = self::STATUS_WAITING;
        $this->message = "";
        return $this;
    }

    public function isWaiting()
    {
        return $this->status === self::STATUS_WAITING;
    }

    public function isRunning()
    {
        return $this->status === self::STATUS_RUNNING;
    }

    public function isFinished()
    {
        return $this->status === self::STATUS_FINISHED;
    }

    public function getStatusName()
    {
        switch ($this->status) {
            case self::STATUS_WAITING:
                return "pause";
            case self::STATUS_RUNNING:
                return "play";
            case self::STATUS_FINISHED:
                return "ok";
            default:
                return "error";
        }
    }

    /**
     * @return $this
     */
    public function setStatusRunning()
    {
        $this->status = "running";
        return $this;
    }

    /**
     * @return $this
     */
    public function setStatusFinished()
    {
        $this->status = "finished";
        return $this;
    }

    /**
     * @return User
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * @param User $updatedBy
     * @return QueueItem
     */
    public function setUpdatedBy($updatedBy)
    {
        $this->updatedBy = $updatedBy;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return QueueItem
     */
    public function setStatus(string $status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     * @return QueueItem
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

}
