<?php

namespace LokiTuoResultBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use LokiUserBundle\Entity\User;

/**
 * Class QueueItem
 * @package LokiTuoResultBundle\Entity
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="LokiTuoResultBundle\Repository\QueueItemRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class QueueItem extends AbstractBaseEntity
{

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="LokiUserBundle\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="id", name="user_id", nullable=false)
     */
    protected $updatedBy;

    /**
     * @var string Name of the Queue Item
     * @ORM\Column(type="string", nullable=false, unique=true)
     */
    protected $name;

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
        $this->status = "waiting";
        $this->message = "";
        return $this;
    }

    public function isWaiting()
    {
        return $this->status === "waiting";
    }

    public function isRunning()
    {
        return $this->status === 'running';
    }

    /**
     * @param string $message
     * @return $this
     */
    public function setStatusRunning(string $message)
    {
        $this->status = "running";
        $this->message = $message;
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
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return QueueItem
     */
    public function setName(string $name)
    {
        $this->name = $name;
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
