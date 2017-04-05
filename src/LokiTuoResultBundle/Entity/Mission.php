<?php

namespace LokiTuoResultBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Mission.
 *
 * @ORM\Table(name="mission")
 * @ORM\Entity(repositoryClass="LokiTuoResultBundle\Repository\MissionRepository")
 * @UniqueEntity({"name", "bge", "structures"})
 * @ORM\HasLifecycleCallbacks()
 */
class Mission extends AbstractBaseEntity
{
    /**
     * @var string
     *
     * @ORM\Column(name="Name", type="string", length=255)
     */
    private $name;
    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $structures;
    /**
     * @var BattleGroundEffect
     *
     * @ORM\ManyToOne(targetEntity="BattleGroundEffect", inversedBy="missions")
     */
    private $bge;

    /**
     * @var string
     *
     * @ORM\Column(name="Type", type="string", length=255)
     */
    private $type;

    /**
     * @var string
     * @ORM\Column(type="string", unique=true)
     */
    private $uuid;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Result", mappedBy="mission", cascade={"remove"})
     */
    private $results;

    public function toUuid(): string
    {
        return self::createUuid($this->getName(), $this->getBge(), $this->getStructures());
    }

    /**
     * Set createdAt.
     *
     * @ORM\PrePersist
     */
    public function setCreatedAt()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->uuid = $this->toUuid();
    }

    /**
     * Set updatedAt.
     *
     * @ORM\PreUpdate
     */
    public function setUpdatedAt()
    {
        $this->updatedAt = new \DateTime();
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return Mission
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set type.
     *
     * @param string $type
     *
     * @return Mission
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return ArrayCollection
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * @param ArrayCollection $results
     */
    public function setResults($results)
    {
        $this->results = $results;
    }

    /**
     * @return string
     */
    public function getStructures()
    {
        return $this->structures;
    }

    /**
     * @param string $structures
     */
    public function setStructures(string $structures)
    {
        $this->structures = $structures;
    }

    /**
     * @return BattleGroundEffect
     */
    public function getBge()
    {
        return $this->bge;
    }

    /**
     * @param BattleGroundEffect $bge
     */
    public function setBge(BattleGroundEffect $bge = null)
    {
        $this->bge = $bge;
    }

    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }

    /**
     * @param string $uuid
     */
    public function setUuid(string $uuid)
    {
        $this->uuid = $uuid;
    }

    public static function createUuid(string $name, BattleGroundEffect $bge = null, string $structures = null): string
    {
        $uuid = 'mission|%s|bge|%s|structures|%s';

        return sprintf($uuid, $name, $bge ? $bge->id : 'null', $structures);
    }
}
