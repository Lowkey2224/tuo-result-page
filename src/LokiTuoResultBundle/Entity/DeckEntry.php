<?php

namespace LokiTuoResultBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Deck
 *
 * @ORM\Table(name="deck_entry")
 * @ORM\Entity(repositoryClass="LokiTuoResultBundle\Repository\DeckEntryRepository")
 * @UniqueEntity(fields={"playOrder", "card", "result"},
 *     message="There exists already this card for this result in this order")
 * @ORM\HasLifecycleCallbacks()
 *
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
     * @var Card
     * @ORM\ManyToOne(targetEntity="Card")
     * @ORM\JoinColumn(referencedColumnName="id", name="card_id")
     */
    private $card;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $level;


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
     * Set playOrder
     *
     * @param integer $playOrder
     *
     * @return DeckEntry
     */
    public function setPlayOrder($playOrder)
    {
        $this->playOrder = $playOrder;

        return $this;
    }

    /**
     * Get playOrder
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

    /**
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @param int $level
     */
    public function setLevel($level)
    {
        $this->level = $level;
    }
}
