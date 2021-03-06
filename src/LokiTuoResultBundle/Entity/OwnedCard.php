<?php

namespace LokiTuoResultBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * OwnedCard.
 *
 * @ORM\Table(name="owned_card")
 * @ORM\Entity(repositoryClass="LokiTuoResultBundle\Repository\OwnedCardRepository")
 * @UniqueEntity(
 *     fields={"level", "player", "card"},
 *     message="Dieser spieler hat diesen Eintrag bereits."
 * )
 * @ORM\HasLifecycleCallbacks()
 */
class OwnedCard extends AbstractBaseEntity
{
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
     * @var CardLevel
     * @ORM\ManyToOne(targetEntity="LokiTuoResultBundle\Entity\CardLevel")
     * @ORM\JoinColumn(referencedColumnName="id", name="card_level_id")
     */
    private $card;

    public function __toString()
    {
        $str = $this->card->getCard()->getName();
        $str .= '-' . $this->card->getLevel();
        if ($this->amount > 1) {
            $str .= ' (' . $this->amount . ')';
        }

        return $str;
    }

    public function toArray()
    {
        return [
            'name' => $this->getCard()->getName(),
            'level' => $this->getCard()->getLevel(),
            'amount' => $this->getAmount(),
            'id' => $this->getId(),
        ];
    }

    /**
     * @return string
     */
    public function toDeckString()
    {
        $str = $this->card->getCard()->getName();
        $str .= '-' . $this->card->getLevel();
        if ($this->amountInDeck > 1) {
            $str .= ' (' . $this->amountInDeck . ')';
        }

        return $str;
    }

    /**
     * Set amount.
     *
     * @param int $amount
     *
     * @return OwnedCard
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount.
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
     * @return CardLevel
     */
    public function getCard()
    {
        return $this->card;
    }

    /**
     * @param CardLevel $card
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
