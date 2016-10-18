<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 17.10.16
 * Time: 15:11
 */

namespace LokiTuoResultBundle\Service\Simulation;


use LokiTuoResultBundle\Entity\Player;

class Simulation
{
    /** @var  array */
    private $missions;

    /** @var  String */
    private $backgroundEffect;

    /** @var  integer */
    private $iterations;

    /** @var  Player[] */
    private $players;

    /** @var  String */
    private $simType;

    /** @var  String */
    private $resultFile;

    /** @var  String */
    private $guild;

    public function __construct()
    {
        $this->iterations = 10000;
        $this->simType = "climb";
        $this->backgroundEffect = "";
        $this->backgroundEffect = "result.txt";
        $this->level = 10;
        $this->guild = [];
        $this->players = [];
    }

    public function setMissions($missions)
    {
        $this->missions = explode(",",$missions);
    }

    public function addMission($mission)
    {
        $this->missions[] = $mission;
    }

    public function addPlayer(Player $player)
    {
        $this->players[] = $player;
    }

    public function setPlayer(array $players)
    {
        $this->players = $players;
    }

    public function setGuild($guild)
    {
        $this->guild = $guild;
    }

    /**
     * @return array
     */
    public function getMissions()
    {
        return $this->missions;
    }

    /**
     * @return String
     */
    public function getBackgroundEffect()
    {
        return $this->backgroundEffect;
    }

    /**
     * @return int
     */
    public function getIterations()
    {
        return $this->iterations;
    }

    /**
     * @return \LokiTuoResultBundle\Entity\Player[]
     */
    public function getPlayers()
    {
        return $this->players;
    }

    /**
     * @return String
     */
    public function getSimType()
    {
        return $this->simType;
    }

    /**
     * @return String
     */
    public function getResultFile()
    {
        return $this->resultFile;
    }

    /**
     * @return String
     */
    public function getGuild()
    {
        return $this->guild;
    }

    /**
     * @param String $backgroundEffect
     */
    public function setBackgroundEffect($backgroundEffect)
    {
        $this->backgroundEffect = $backgroundEffect;
    }

    /**
     * @param int $iterations
     */
    public function setIterations($iterations)
    {
        $this->iterations = $iterations;
    }

    /**
     * @param \LokiTuoResultBundle\Entity\Player[] $players
     */
    public function setPlayers($players)
    {
        $this->players = $players;
    }

    /**
     * @param String $simType
     */
    public function setSimType($simType)
    {
        $this->simType = $simType;
    }

    /**
     * @param String $resultFile
     */
    public function setResultFile($resultFile)
    {
        $this->resultFile = $resultFile;
    }




}