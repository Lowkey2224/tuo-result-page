<?php

namespace LokiTuoResultBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Player
 *
 * @ORM\Table(name="player")
 * @ORM\Entity(repositoryClass="LokiTuoResultBundle\Repository\PlayerRepository")
 */
class Player extends AbstractBaseEntity
{


    /**
     * @var string
     *
     * @ORM\Column(name="Name", type="string", length=255)
     */
    private $name;

    /**
     * @var ArrayCollection[]
     * @ORM\OneToMany(targetEntity="Result", mappedBy="player")
     */
    private $results;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="OwnedCard", mappedBy="player")
     */
    private $ownedCards;

    public function __construct()
    {
        $this->results = new ArrayCollection();
        $this->ownedCards = new ArrayCollection();
    }

    public function getGuild()
    {
        if ($this->results->isEmpty()) {
            return "";
        }

        return $this->results->last()->getGuild();
    }

    /**
     * @return ArrayCollection[]
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * @param ArrayCollection[] $results
     */
    public function setResults($results)
    {
        $this->results = $results;
    }


    /**
     * Set name
     *
     * @param string $name
     *
     * @return Player
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return ArrayCollection
     */
    public function getOwnedCards()
    {
        return $this->ownedCards;
    }

    /**
     * @param ArrayCollection $ownedCards
     */
    public function setOwnedCards($ownedCards)
    {
        $this->ownedCards = $ownedCards;
    }
}
