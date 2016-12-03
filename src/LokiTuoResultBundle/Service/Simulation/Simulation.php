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

    /** @var  array */
    private $structures;

    /** @var  array */
    private $enemyStructures;

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

    /** @var  string */
    private $scriptType;

    public function __construct()
    {
        $this->iterations = 10000;
        $this->simType = "climb";
        $this->backgroundEffect = "";
        $this->resultFile = "result.txt";
        $this->guild = [];
        $this->players = [];
        $this->scriptType = "shell";
        $this->structures = [];
    }

    public function setStructures($structures)
    {
        $this->structures = explode(",", $structures);
        $this->structures = array_map(function($element){ return trim($element);}, $this->structures);
    }

    /**
     * @param array $enemyStructures
     */
    public function setEnemyStructures($enemyStructures)
    {
        $this->enemyStructures = explode(",", $enemyStructures);
        $this->enemyStructures = array_map(function($element){ return trim($element);}, $this->enemyStructures);
    }


    public function setMissions($missions)
    {
        $this->missions = explode(",", $missions);
        $this->missions = array_map(function($element){ return trim($element);}, $this->missions);
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

    /**
     * @return string
     */
    public function getScriptType()
    {
        return $this->scriptType;
    }

    /**
     * @param string $scriptType
     */
    public function setScriptType($scriptType)
    {
        $this->scriptType = $scriptType;
    }

    /**
     * @return array
     */
    public function getStructures()
    {
        return $this->structures;
    }

    /**
     * @return array
     */
    public function getEnemyStructures()
    {
        return $this->enemyStructures;
    }


}
