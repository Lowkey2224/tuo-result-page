<?php

namespace LokiTuoResultBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * OwnedCard
 *
 * @ORM\Table(name="owned_card")
 * @ORM\Entity(repositoryClass="LokiTuoResultBundle\Repository\OwnedCardRepository")
 * @UniqueEntity(
 *     fields={"level", "amount", "player", "card", "inCurrentDeck"},
 *     message="Dieser spieler hat diesen Eintrag bereits."
 * )
 * @ORM\HasLifecycleCallbacks()
 */
class OwnedCard extends AbstractBaseEntity
{


    /**
     * @var int
     *
     * @ORM\Column(name="level", type="integer", nullable=true)
     */
    private $level;

    /**
     * @var int
     *
     * @ORM\Column(name="amount", type="integer")
     */
    private $amount = 1;


    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $amountInDeck = 0;


    /**
     * @var Player
     * @ORM\ManyToOne(targetEntity="Player", inversedBy="ownedCards")
     * @ORM\JoinColumn(referencedColumnName="id", name="player_id")
     */
    private $player;

    /**
     * @var Card
     * @ORM\ManyToOne(targetEntity="Card")
     * @ORM\JoinColumn(referencedColumnName="id", name="card_id")
     */
    private $card;

    public function __toString()
    {

        $str = $this->card->getName();
        if ($this->level) {
            $str.="-".$this->level;
        }
        if ($this->amount>1) {
            $str.=" (".$this->amount.")";
        }
        return $str;
    }

    /**
     * Set level
     *
     * @param integer $level
     *
     * @return OwnedCard
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Get level
     *
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Set amount
     *
     * @param integer $amount
     *
     * @return OwnedCard
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return Player
     */
    public function getPlayer()
    {
        return $this->player;
    }

    /**
     * @param Player $player
     */
    public function setPlayer($player)
    {
        $this->player = $player;
    }

    /**
     * @return Card
     */
    public function getCard()
    {
        return $this->card;
    }

    /**
     * @param Card $card
     */
    public function setCard($card)
    {
        $this->card = $card;
    }

    /**
     * @return int
     */
    public function getAmountInDeck()
    {
        return $this->amountInDeck;
    }

    /**
     * @param int $amountInDeck
     */
    public function setAmountInDeck($amountInDeck)
    {
        $this->amountInDeck = $amountInDeck;
    }
}
