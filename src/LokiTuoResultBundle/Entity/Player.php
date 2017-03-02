<?php

namespace LokiTuoResultBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use LokiUserBundle\Entity\User;

/**
 * Player
 *
 * @ORM\Table(name="player")
 * @ORM\Entity(repositoryClass="LokiTuoResultBundle\Repository\PlayerRepository")
 * @ORM\HasLifecycleCallbacks()
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

    /**
     * @var String
     * @ORM\Column(name="currentGuild", type="string", length=20)
     */
    private $currentGuild;

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    private $active;
    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="LokiUserBundle\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="id", name="user_id")
     */
    private $owner;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $ownershipConfirmed;

    public function __construct()
    {
        $this->results = new ArrayCollection();
        $this->ownedCards = new ArrayCollection();
        $this->active = true;
        $this->ownershipConfirmed = false;
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getFullName()
    {
        return "[".$this->getCurrentGuild()."] ".$this->getName();
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
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDeck()
    {
        return $this->getOwnedCards()->filter(function (OwnedCard $ownedCard) {
            return $ownedCard->getAmountInDeck() > 0;
        });
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

    /**
     * @return String
     */
    public function getCurrentGuild()
    {
        return $this->currentGuild;
    }

    /**
     * @param String $currentGuild
     */
    public function setCurrentGuild($currentGuild)
    {
        $this->currentGuild = $currentGuild;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive(bool $active)
    {
        $this->active = $active;
    }

    /**
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param User $owner
     */
    public function setOwner(User $owner)
    {
        $this->owner = $owner;
    }

    /**
     * @return bool
     */
    public function isOwnershipConfirmed(): bool
    {
        return $this->ownershipConfirmed;
    }

    /**
     * @param bool $ownershipConfirmed
     */
    public function setOwnershipConfirmed(bool $ownershipConfirmed)
    {
        $this->ownershipConfirmed = $ownershipConfirmed;
    }


}
