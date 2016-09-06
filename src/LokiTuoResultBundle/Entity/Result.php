<?php

namespace LokiTuoResultBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
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
     * @var integer
     *
     * @ORM\Column(name="percent", type="integer", nullable=false)
     */
    private $percent;

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
     * @var DeckEntry[]
     * @ORM\OneToMany(targetEntity="DeckEntry", mappedBy="result", cascade={"persist"}, fetch="EAGER")
     */
    private $deck;

    /**
     * @var ResultFile
     * @ORM\ManyToOne(targetEntity="ResultFile", inversedBy="results")
     * @ORM\JoinColumn(referencedColumnName="id", name="file_id")
     */
    private $sourceFile;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $guild = "CTP";

    public function __construct()
    {
        $this->deck = new ArrayCollection();
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
     * @return ArrayCollection
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

    /**
     * @return int
     */
    public function getPercent()
    {
        return $this->percent;
    }

    /**
     * @param int $percent
     */
    public function setPercent($percent)
    {
        $this->percent = $percent;
    }

    /**
     * @return ResultFile
     */
    public function getSourceFile()
    {
        return $this->sourceFile;
    }

    /**
     * @param ResultFile $sourceFile
     */
    public function setSourceFile($sourceFile)
    {
        $this->sourceFile = $sourceFile;
    }

    /**
     * @return string
     */
    public function getGuild()
    {
        return $this->guild;
    }

    /**
     * @param string $guild
     */
    public function setGuild($guild)
    {
        $this->guild = $guild;
    }
}
