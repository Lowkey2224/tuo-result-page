<?php

namespace LokiTuoResultBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Card.
 *
 * @ORM\Table(name="guild")
 * @ORM\Entity(repositoryClass="LokiTuoResultBundle\Repository\GuildRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Guild extends AbstractBaseEntity
{
    /**
     * @var string
     * @ORM\Column(type="string", nullable=false, unique=true)
     */
    private $name;

    /**
     * @var ArrayCollection|Player[]
     * @ORM\OneToMany(targetEntity="LokiTuoResultBundle\Entity\Player", mappedBy="guild")
     */
    private $players;

    /**
     * @var ArrayCollection|Result[]
     * @ORM\OneToMany(targetEntity="LokiTuoResultBundle\Entity\Result", mappedBy="guild")
     */
    private $results;

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    private $enabled;

    public function __toString()
    {
        return $this->getName() . "";
    }

    public function __construct()
    {
        $this->players = new ArrayCollection();
        $this->results = new ArrayCollection();
        $this->enabled = true;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return ArrayCollection
     */
    public function getPlayers(): ArrayCollection
    {
        return $this->players;
    }

    /**
     * @param ArrayCollection $players
     */
    public function setPlayers(ArrayCollection $players)
    {
        $this->players = $players;
    }

    /**
     * @return ArrayCollection
     */
    public function getResults(): ArrayCollection
    {
        return $this->results;
    }

    /**
     * @param ArrayCollection $results
     */
    public function setResults(ArrayCollection $results)
    {
        $this->results = $results;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled(bool $enabled)
    {
        $this->enabled = $enabled;
    }


}
