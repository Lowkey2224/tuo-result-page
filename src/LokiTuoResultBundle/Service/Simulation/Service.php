<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 30.08.16
 * Time: 08:56
 */

namespace LokiTuoResultBundle\Service\Simulation;

use LokiTuoResultBundle\Entity\OwnedCard;
use LokiTuoResultBundle\Entity\Player;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

class Service
{

    use LoggerAwareTrait;

    private $resultFileName = "result.txt";
    private $iterations = 10000;
    private $simType = "climb";

    public function __construct()
    {
        $this->logger = new NullLogger();
    }

    /**
     *
     * @param string[] $mission The Mission names
     * @param Player[] $players
     * @return string
     */
    public function createSimulation(array $mission, array $players = [])
    {
        $script = "echo \"CTF Results 1,3,6-24\"\n";
        $script .= "echo \"CTF Results 1,3,6-24\" > ./result.txt\n";
        $script .= $this->getMemberCards($players);
        $script .= "\n\n";
        $script .= $this->getExecutionLines($mission, $players, $this->simType);

        return $script;
    }

    public function getSimulation(Simulation $simulation)
    {
        $script = "echo \"".$simulation->getGuild()." Results 1,3,6-24\"\n";
        $script .= "echo \"".$simulation->getGuild()." 1,3,6-24\" > ./result.txt\n";
        $script .= $this->getMemberCards($simulation->getPlayers());
        $script .= "\n\n";
        $script .= $this->getExecutionLines($simulation->getMissions(), $simulation->getPlayers(), $simulation->getSimType());

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
     * @param string[] $missions
     * @param Player[] $players
     * @return string
     */
    private function getExecutionLines( $missions,  $players, $simTypes)
    {
        $result = "";
        foreach ($players as $player) {
            $result .= $this->getMissionExecutionsForPlayer($missions, $player, $simTypes);
        }

        return $result;
    }

    /**
     * @param string[] $missions
     * @param Player $player
     * @return string
     */
    private function getMissionExecutionsForPlayer($missions, Player $player, $simType)
    {
        $str = "";
        $now = new \DateTime();
        $deck = $player->getOwnedCards()->filter(function (OwnedCard $ownedCard) {
            return $ownedCard->getAmountInDeck() > 0;
        });

        $now = $now->format('m/d/y/h/i/s');
        foreach ($missions as $mission) {
            $str .= 'echo "member name ' . $player->getName() . '@'
                . $now . ' against ' . $mission . '"' . "\n";
            $str .= 'echo "member name ' . $player->getName() . '@'
                . $now . ' against ' . $mission . '" >> ./' . $this->resultFileName . "\n";
            $str .= './tuo "' . implode(", ", $deck->toArray()) . '" "'
                . $mission . '" -o="$MemberDeck' . $player->getId() . '" -r '.$simType.' '
                . $this->iterations . ' > ./tempRes.txt' . "\n";
            $str .= 'tail -1 ./tempRes.txt | head -1 >> ./' . $this->resultFileName . "\n\n";
        }
        return $str;
    }
}
