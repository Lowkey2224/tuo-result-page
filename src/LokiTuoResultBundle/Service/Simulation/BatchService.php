<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 21.10.16
 * Time: 15:50
 */

namespace LokiTuoResultBundle\Service\Simulation;


use LokiTuoResultBundle\Entity\OwnedCard;
use LokiTuoResultBundle\Entity\Player;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

class BatchService implements SimulationCreatorInterface
{

    use LoggerAwareTrait;

    public function __construct()
    {
        $this->logger = new NullLogger();
    }

    public function getSimulation(Simulation $simulation)
    {


        $script = "echo \"" . $simulation->getGuild() . " Results 1-50\"\n";
        $script .= "setlocal EnableDelayedExpansion\n";
        $script .= "CLS\n";
        $script .= "@ECHO OFF\n";
        $script .= "\n";
        $script .= "echo \"" . $simulation->getGuild() . " Results 1-50\"\n";
        $script .= "SET FILENAME=%~n0\n";
        $script .= "SET FILENAME=%FILENAME: =%\n";
        $script .= "SET \"TARGET=Result_%FILENAME%.txt\"\n";
        $script .= "SET \"TEMPRES=tempRes_%FILENAME%.txt\"\n";
        $script .= "SET /A NBSIM=0\n";
        $script .= "SET STARTTIME=%TIME%\n";
        $script .= "echo \"CTP Results 1,6-7,9-11,15-16,18-21,23,25,28-31,33,36-37,40-47,49-50\" > %TARGET%\n";
        $script .= "\n";
        $script .= $this->getMemberCards($simulation->getPlayers());
        $script .= "\n\n";

        $numberOfSims = count($simulation->getPlayers()) * count($simulation->getMissions());
        $script .= "echo Number of sims pending : " . $numberOfSims . "\n";

        $script .= "\n\n";
        $script .= $this->getExecutionLines($simulation);
        $script .= "del %TEMPRES%
SET ENDTIME=%TIME%
echo Sims Done : %NBSIM%
echo START :  %STARTTIME%
echo FINISH : %ENDTIME%
pause\n";


        return $script;
    }


    /**
     * @param Player[] $players
     * @return string
     */
    private function getMemberCards($players)
    {
        $string = "";
        foreach ($players as $player) {
            $owned = $player->getOwnedCards();

            $string .= "SET MemberDeck" . $player->getId() . "=\"" . implode(", ", $owned->toArray()) . "\"\n";
        }

        return $string;
    }

    /**
     * @param Simulation $simulation
     * @return string
     */
    private function getExecutionLines(Simulation $simulation)
    {
        $players = $simulation->getPlayers();
        $result = "";
        foreach ($players as $player) {
            $result .= $this->getMissionExecutionsForPlayer($player, $simulation);
        }

        return $result;
    }


    /**
     * @param Simulation $simulation
     * @return string
     */
    private function getMissionExecutionsForPlayer(Player $player, Simulation $simulation)
    {

        $str = "";
        $now = new \DateTime();
        $deck = $player->getOwnedCards()->filter(function (OwnedCard $ownedCard) {
            return $ownedCard->getAmountInDeck() > 0;
        });

        $now = $now->format('m/d/y/h/i/s');
        foreach ($simulation->getMissions() as $mission) {
            //Output for Console
            $str .= 'echo "member name ' . $player->getName() . '@';
            $str .= $now . ' against ' . $mission . '"' . "\n";
            //Output Name of Sim for resultFile
            $str .= 'echo "member name ' . $player->getName() . '@';
            $str .= $now . ' against ' . $mission . "\" >> ./ %TARGET%\n";
            //Simulation Call
            $str .= 'tuo.exe "' . implode(", ", $deck->toArray()) . '" "';
            $str .= $mission . '" -o="$MemberDeck' . $player->getId() . '"';
            if (!empty($simulation->getStructures())) {
                $str .= ' yf "' . implode(", ", $simulation->getStructures()) . '"';
            }
            $str .= ' -r ' . $simulation->getSimType() . ' ';
            $str .= $simulation->getIterations() . " > %TEMPRES%\n";
            //Somehow find the best result in tempRes.txt
            $str .= "set \"line=\"";
            $str .= "for /f \"tokens=*\" %%b in ('findstr Optimized %TEMPRES%') do set \"line=%%b\"\n";
            $str .= "for /f \"tokens=*\" %%b in ('findstr /b /l win %TEMPRES%') do set \"line=%%b\"\n";
            //Move Result to Resultfile
            $str .= "echo %line% >> %TARGET%\n";
            $str .= "SET /A NBSIM+=1\n\n";


        }
        return $str;
    }
}