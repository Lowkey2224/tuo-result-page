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

class ShellService implements SimulationCreatorInterface
{
    use LoggerAwareTrait;

    public function __construct()
    {
        $this->logger = new NullLogger();
    }

    public function getSimulation(Simulation $simulation)
    {
        $script = "echo \"" . $simulation->getGuild() . " Results 1,3,6-24\"\n";
        $script .= "echo \"" . $simulation->getGuild() . " Results 1,3,6-24\" > ./result.txt\n";
        $script .= $this->getMemberCards($simulation->getPlayers());
        $script .= "\n\n";

        $numberOfSims = count($simulation->getPlayers()) * count($simulation->getMissions());
        $script .= "echo Number of sims pending : ".$numberOfSims."\n";

        $script .= $this->getExecutionLines($simulation);

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

            $string .= "MemberDeck" . $player->getId() . "=\"" . implode(", ", $owned->toArray()) . "\"\n";
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
            $str .= 'echo "member name ' . $player->getName() . '@';
            $str .= $now . ' against ' . $mission . '"' . "\n";
            $str .= 'echo "member name ' . $player->getName() . '@';
            $str .= $now . ' against ' . $mission . '" >> ./' . $simulation->getResultFile() . "\n";
            $str .= './tuo "' . implode(", ", $deck->toArray()) . '" "';
            $str .= $mission . '" -o="$MemberDeck' . $player->getId().'"';
            if (!empty($simulation->getStructures())) {
                $str.= ' yf "'. implode(", ", $simulation->getStructures()).'"';
            }
            if (!empty($simulation->getEnemyStructures())) {
                $str.= ' ef "'. implode(", ", $simulation->getEnemyStructures()).'"';
            }
            $str .= ' -r ' . $simulation->getSimType() . ' ';
            $str .= $simulation->getIterations() . ' > ./tempRes.txt' . "\n";
            $str .= 'tail -1 ./tempRes.txt | head -1 >> ./' . $simulation->getResultFile() . "\n\n";
        }
        return $str;
    }
}
