<?php

namespace LokiTuoResultBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Deck
 *
 * @ORM\Table(name="deck")
 * @ORM\Entity(repositoryClass="LokiTuoResultBundle\Repository\DeckRepository")
 * @UniqueEntity(fields={"playOrder", "card", "mission"}, message="There already exists a repair job with name {{ value }} for this device and these colours.")

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
     * @var Mission
     * @ORM\ManyToOne(targetEntity="Mission")
     * @ORM\JoinColumn(referencedColumnName="id", name="mission_id")
     */
    private $mission;

    /**
     * @var Card
     * @ORM\ManyToOne(targetEntity="Card")
     * @ORM\JoinColumn(referencedColumnName="id", name="card_id")
     */
    private $card;

    /**
     * @return Mission
     */
    public function getMission()
    {
        return $this->mission;
    }

    /**
     * @param Mission $mission
     */
    public function setMission($mission)
    {
        $this->mission = $mission;
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
}

