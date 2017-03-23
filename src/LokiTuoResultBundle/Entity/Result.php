<?php

namespace LokiTuoResultBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Result.
 *
 * @ORM\Table(name="result")
 * @ORM\Entity(repositoryClass="LokiTuoResultBundle\Repository\ResultRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Result extends AbstractBaseEntity
{
    /**
     * @var int
     *
     * @ORM\Column(name="percent", type="integer", nullable=false)
     */
    private $percent;

    /**
     * @var Mission
     * @ORM\ManyToOne(targetEntity="Mission", inversedBy="results")
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
     * @ORM\OneToMany(targetEntity="DeckEntry", mappedBy="result", cascade={"persist", "remove"}, fetch="EAGER")
     */
    private $deck;

    /**
     * @var ResultFile
     * @ORM\ManyToOne(targetEntity="ResultFile", inversedBy="results")
     * @ORM\JoinColumn(referencedColumnName="id", name="file_id")
     */
    private $sourceFile;

    /**
     * @var Guild
     * @ORM\ManyToOne(targetEntity="LokiTuoResultBundle\Entity\Guild", inversedBy="results")
     * @ORM\JoinColumn(referencedColumnName="id", name="guild_id")
     */
    private $guild;

    public function __construct()
    {
        $this->deck = new ArrayCollection();
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
     * @param DeckEntry[] $deck
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
     * @return Guild
     */
    public function getGuild(): Guild
    {
        return $this->guild;
    }

    /**
     * @param Guild $guild
     */
    public function setGuild(Guild $guild)
    {
        $this->guild = $guild;
    }
}
