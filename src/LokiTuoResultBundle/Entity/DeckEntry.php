<?php

namespace LokiTuoResultBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Deck.
 *
 * @ORM\Table(name="deck_entry")
 * @ORM\Entity(repositoryClass="LokiTuoResultBundle\Repository\DeckEntryRepository")
 * @UniqueEntity(fields={"playOrder", "card", "result"},
 *     message="There exists already this card for this result in this order")
 * @ORM\HasLifecycleCallbacks()
 */
class DeckEntry extends AbstractBaseEntity
{
    /**
     * @var int
     *
     * @ORM\Column(name="playOrder", type="integer")
     */
    private $playOrder;

    /**
     * @var Result
     * @ORM\ManyToOne(targetEntity="Result", inversedBy="deck")
     * @ORM\JoinColumn(referencedColumnName="id", name="result_id")
     */
    private $result;

    /**
     * @var CardLevel
     * @ORM\ManyToOne(targetEntity="CardLevel")
     * @ORM\JoinColumn(referencedColumnName="id", name="card_level_id")
     */
    private $card;

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
     * Set playOrder.
     *
     * @param int $playOrder
     *
     * @return DeckEntry
     */
    public function setPlayOrder($playOrder)
    {
        $this->playOrder = $playOrder;

        return $this;
    }

    /**
     * Get playOrder.
     *
     * @return int
     */
    public function getPlayOrder()
    {
        return $this->playOrder;
    }

    /**
     * @return Result
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param Result $result
     */
    public function setResult($result)
    {
        $this->result = $result;
    }
}
