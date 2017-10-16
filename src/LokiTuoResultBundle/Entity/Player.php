<?php

namespace LokiTuoResultBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use LokiUserBundle\Entity\User;

/**
 * Player.
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
     * @var Guild
     * @ORM\ManyToOne(targetEntity="LokiTuoResultBundle\Entity\Guild", inversedBy="players")
     * @ORM\JoinColumn(referencedColumnName="id", name="guild_id")
     */
    private $guild;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $active;
    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="LokiUserBundle\Entity\User", inversedBy="players")
     * @ORM\JoinColumn(referencedColumnName="id", name="user_id")
     */
    private $owner;

    /**
     * @var KongregateCredentials
     * @ORM\OneToOne(targetEntity="LokiTuoResultBundle\Entity\KongregateCredentials", cascade={"persist"})
     * @ORM\JoinColumn(name="kong_credentials_id", referencedColumnName="id")
     */
    private $kongCredentials;

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
        $this->kongCredentials = new KongregateCredentials();
    }

    /**
     * Checks if the User has Credentials usable with the Tyrant Unleashed API
     * @return bool
     */
    public function hasKongCredentials()
    {
        return $this->kongCredentials && $this->kongCredentials->isValid();
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getFullName()
    {
        return '[' . $this->getGuild() . '] ' . $this->getName();
    }

    /**
     * @return Guild
     */
    public function getGuild()
    {
        return $this->guild;
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
     * Set name.
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
     * Get name.
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
    public function setOwner(User $owner = null)
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
     *
     * @throws \Exception
     */
    public function setOwnershipConfirmed(bool $ownershipConfirmed)
    {
        if ($ownershipConfirmed && !$this->getOwner()) {
            throw new \Exception('You cant confirm Ownership for a Player that hasnt been claimed');
        }
        $this->ownershipConfirmed = $ownershipConfirmed;
    }

    public function isOwnedBy(User $user)
    {
        return $this->isOwnershipConfirmed() && $this->getOwner()->getId() == $user->getId();
    }

    /**
     * @param Guild $guild
     */
    public function setGuild(Guild $guild)
    {
        $this->guild = $guild;
    }

    /**
     * @return KongregateCredentials
     */
    public function getKongCredentials(): KongregateCredentials
    {
        return $this->kongCredentials;
    }

    /**
     * @param KongregateCredentials $kongCredentials
     * @return Player
     */
    public function setKongCredentials(KongregateCredentials $kongCredentials)
    {
        $this->kongCredentials = $kongCredentials;
        return $this;
    }
}
