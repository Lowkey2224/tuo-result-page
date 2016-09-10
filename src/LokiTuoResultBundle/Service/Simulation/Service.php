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
        $script .= $this->getExecutionLines($mission, $players);

        return $script;
    }

    /**
     * @param Player[] $players
     * @return string
     */
    private function getMemberCards(array $players)
    {
        $string = "";
        foreach ($players as $player) {
            $owned = $player->getOwnedCards()->filter(function (OwnedCard $ownedCard) {
                return !$ownedCard->isInCurrentDeck();
            });

            $string .= "MemberDeck" . $player->getId() . "=\"" . implode(", ", $owned->toArray()) . "\"\n";
        }

        return $string;
    }

    /**
     * @param string[] $missions
     * @param Player[] $players
     * @return string
     */
    private function getExecutionLines(array $missions, array $players)
    {
        $result = "";
        foreach ($players as $player) {
            $result .= $this->getMissionExecutionsForPlayer($missions, $player);
        }

        return $result;
    }

    /**
     * @param string[] $missions
     * @param Player $player
     * @return string
     */
    private function getMissionExecutionsForPlayer(array $missions, Player $player)
    {
        $str = "";
        $now = new \DateTime();
        $deck = $player->getOwnedCards()->filter(function (OwnedCard $ownedCard) {
            return $ownedCard->isInCurrentDeck();
        });

        $now = $now->format('m/d/y/h/i/s');
        foreach ($missions as $mission) {
            $str .= 'echo "member name ' . $player->getName() . '@'
                . $now . ' against ' . $mission . '"' . "\n";
            $str .= 'echo "member name ' . $player->getName() . '@'
                . $now . ' against ' . $mission . '" >> ./' . $this->resultFileName . "\n";
            $str .= './tuo "' . implode(", ", $deck->toArray()) . '" "'
                . $mission . '" -o="$MemberDeck' . $player->getId() . '" -r climb '
                . $this->iterations . ' > ./tempRes.txt' . "\n";
            $str .= 'tail -1 ./tempRes.txt | head -1 >> ./' . $this->resultFileName . "\n\n";
        }
        return $str;
    }
}
