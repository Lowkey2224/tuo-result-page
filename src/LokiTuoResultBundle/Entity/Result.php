<?php

namespace LokiTuoResultBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Result
 *
 * @ORM\Table(name="result")
 * @ORM\Entity(repositoryClass="LokiTuoResultBundle\Repository\ResultRepository")
 */
class Result
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
     * @var Mission
     * @ORM\ManyToOne(targetEntity="Mission")
     * @ORM\JoinColumn(referencedColumnName="id", name="mission_id")
     */
    private $mission;

    /**
     * @var Player
     * @ORM\ManyToOne(targetEntity="Player", inversedBy="results")
     * @ORM\JoinColumn(referencedColumnName="id", name="Player_id")
     */
    private $player;

    /**
     * @var Card[]
     * @ORM\ManyToMany(targetEntity="Card")
     * @ORM\JoinTable(name="decks",
     *      joinColumns={@ORM\JoinColumn(name="result_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="card_id", referencedColumnName="id")}
     * )
     */
    private $deck;


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
     * @return Card[]
     */
    public function getDeck()
    {
        return $this->deck;
    }

    /**
     * @param Card[] $deck
     */
    public function setDeck($deck)
    {
        $this->deck = $deck;
    }


}

