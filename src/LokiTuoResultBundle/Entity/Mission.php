<?php

namespace LokiTuoResultBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Mission.
 *
 * @ORM\Table(name="mission")
 * @ORM\Entity(repositoryClass="LokiTuoResultBundle\Repository\MissionRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Mission extends AbstractBaseEntity
{
    /**
     * @var string
     *
     * @ORM\Column(name="Name", type="string", length=255, unique=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="Type", type="string", length=255)
     */
    private $type;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Result", mappedBy="mission", cascade={"remove"})
     */
    private $results;

    public function getGuilds()
    {
        $guilds = [];
        /** @var Result $result */
        foreach ($this->results as $result) {
            $guilds[$result->getGuild()] = $result->getGuild();
        }

        return $guilds;
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
}
