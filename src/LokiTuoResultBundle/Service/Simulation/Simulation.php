<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 17.10.16
 * Time: 15:11.
 */

namespace LokiTuoResultBundle\Service\Simulation;

use LokiTuoResultBundle\Entity\BattleGroundEffect;
use LokiTuoResultBundle\Entity\Player;

/**
 * Class to Reperesnt a MassSimulation
 * Class Simulation.
 */
class Simulation
{
    /** @var array */
    private $missions;

    /** @var array */
    private $structures;

    /** @var array */
    private $enemyStructures;

    /** @var BattleGroundEffect */
    private $backgroundEffect;

    /** @var int */
    private $iterations;

    /** @var Player[] */
    private $players;

    /** @var string */
    private $simType;

    /** @var string */
    private $resultFile;

    /** @var string */
    private $guild;

    /** @var string */
    private $scriptType;

    /** @var int the number of Threads used for Simulation */
    private $threadCount;

    /** @var bool */
    private $ordered;

    /** @var bool true if enemy goes first */
    private $surge;

    /**
     * Simulation constructor.
     */
    public function __construct()
    {
        $this->iterations       = 10000;
        $this->simType          = 'climb';
        $this->backgroundEffect = null;
        $this->resultFile       = 'result.txt';
        $this->guild            = [];
        $this->players          = [];
        $this->scriptType       = 'shell';
        $this->structures       = [];
        $this->enemyStructures  = [];
        $this->threadCount      = 4;
        $this->ordered          = true;
        $this->surge            = false;
    }

    public function getName(int $i = 0): string
    {
        $bge      = $this->getBackgroundEffect();
        $missions = $this->getMissions();
        $str      = $missions[$i];
        if ($bge) {
            $str .= ' with '.$bge->getName();
        }

        return $str;
    }

    /**
     * @return bool
     */
    public function isOrdered()
    {
        return $this->ordered;
    }

    /**
     * @param bool $ordered
     */
    public function setOrdered($ordered)
    {
        $this->ordered = $ordered;
    }

    /**
     * Calculate the numbet Of Simulations in the Script.
     *
     * @return int
     */
    public function getNumberOfSimulations()
    {
        return count($this->getPlayers()) * count($this->getMissions());
    }

    /**
     * @param $structures
     */
    public function setStructures($structures)
    {
        $this->structures = explode(',', $structures);
        $this->structures = array_map(function ($element) {
            return trim($element);
        }, $this->structures);
    }

    /**
     * @param array $enemyStructures
     */
    public function setEnemyStructures($enemyStructures)
    {
        $this->enemyStructures = explode(',', $enemyStructures);
        $this->enemyStructures = array_map(function ($element) {
            return trim($element);
        }, $this->enemyStructures);
    }

    /**
     * @param $missions
     */
    public function setMissions($missions)
    {
        $this->missions = explode(',', $missions);
        $this->missions = array_map(function ($element) {
            return trim($element);
        }, $this->missions);
    }

    /**
     * @param $mission
     */
    public function addMission($mission)
    {
        $this->missions[] = $mission;
    }

    /**
     * @param Player $player
     */
    public function addPlayer(Player $player)
    {
        $this->players[] = $player;
    }

    /**
     * @param array $players
     */
    public function setPlayer(array $players)
    {
        $this->players = $players;
    }

    /**
     * @param $guild
     */
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
     * @return BattleGroundEffect
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
     * @return string
     */
    public function getSimType()
    {
        return $this->simType;
    }

    /**
     * @return string
     */
    public function getResultFile()
    {
        return $this->resultFile;
    }

    /**
     * @return string
     */
    public function getGuild()
    {
        return $this->guild;
    }

    /**
     * @param BattleGroundEffect $backgroundEffect
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
     * @param string $simType
     */
    public function setSimType($simType)
    {
        $this->simType = $simType;
    }

    /**
     * @param string $resultFile
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

    /**
     * @return int
     */
    public function getThreadCount()
    {
        return $this->threadCount;
    }

    /**
     * @param int $threadCount
     */
    public function setThreadCount($threadCount)
    {
        $this->threadCount = $threadCount;
    }

    /**
     * @return bool
     */
    public function isSurge(): bool
    {
        return $this->surge;
    }

    /**
     * @param bool $surge
     */
    public function setSurge(bool $surge)
    {
        $this->surge = $surge;
    }
}
