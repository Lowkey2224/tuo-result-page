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
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $ownershipConfirmed;


    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $kongPassword = "b2541a76c4739991be8462ded7816c5b";

    /**
     * @var string
     * @ORM\Column(type="integer", nullable=true)
     */
    private $tuUserId = 10140147;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $synCode = "03f475cb8d8840be77787acc354d42ddaa51da44295941c27eacb38bb894e4ea";

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $kongUserName = "LokiMcFly";

    /**
     * @var string
     * @ORM\Column(type="integer", nullable=true)
     */
    private $kongId = 5837616;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $kongToken = "ed2599b605e3556b4d8f7471078b7c0e3d41c0b435296f3d4b00dc7ec9515218";

    public function __construct()
    {
        $this->results            = new ArrayCollection();
        $this->ownedCards         = new ArrayCollection();
        $this->active             = true;
        $this->ownershipConfirmed = false;
    }

    /**
     * Checks if the User has Credentials usable with the Tyrant Unleashed API
     * @return bool
     */
    public function hasKongCredentials()
    {
        return $this->kongToken
        && $this->kongId
        && $this->kongPassword
        && $this->tuUserId
        && $this->synCode
        && $this->kongUserName;
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
     * @return string
     */
    public function getKongPassword()
    {
        return $this->kongPassword;
    }

    /**
     * @param string $kongPassword
     * @return Player
     */
    public function setKongPassword(string $kongPassword)
    {
        $this->kongPassword = $kongPassword;
        return $this;
    }

    /**
     * @return int
     */
    public function getTuUserId()
    {
        return $this->tuUserId;
    }

    /**
     * @param int $tuUserId
     * @return Player
     */
    public function setTuUserId(int $tuUserId)
    {
        $this->tuUserId = $tuUserId;
        return $this;
    }

    /**
     * @return string
     */
    public function getSynCode()
    {
        return $this->synCode;
    }

    /**
     * @param string $synCode
     * @return Player
     */
    public function setSynCode(string $synCode)
    {
        $this->synCode = $synCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getKongUserName()
    {
        return $this->kongUserName;
    }

    /**
     * @param string $kongUserName
     * @return Player
     */
    public function setKongUserName(string $kongUserName)
    {
        $this->kongUserName = $kongUserName;
        return $this;
    }

    /**
     * @return int
     */
    public function getKongId()
    {
        return $this->kongId;
    }

    /**
     * @param int $kongId
     * @return Player
     */
    public function setKongId(int $kongId)
    {
        $this->kongId = $kongId;
        return $this;
    }

    /**
     * @return string
     */
    public function getKongToken()
    {
        return $this->kongToken;
    }

    /**
     * @param string $kongToken
     * @return Player
     */
    public function setKongToken(string $kongToken)
    {
        $this->kongToken = $kongToken;
        return $this;
    }
}
