<?php

namespace LokiTuoResultBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Deck
 *
 * @ORM\Table(name="deck")
 * @ORM\Entity(repositoryClass="LokiTuoResultBundle\Repository\DeckRepository")
 * @UniqueEntity(fields={"playOrder", "card", "result"},
 *     message="There exists already this card for this result in this order")
 *
 */
class Deck
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

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
     * @var Result
     */


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
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set playOrder
     *
     * @param integer $playOrder
     *
     * @return Deck
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
}
