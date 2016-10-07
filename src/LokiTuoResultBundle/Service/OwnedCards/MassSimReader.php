<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 16.08.16
 * Time: 18:49
 */

namespace LokiTuoResultBundle\Service\OwnedCards;

use Doctrine\ORM\EntityManager;
use LokiTuoResultBundle\Entity\Player;
use LokiTuoResultBundle\Service\OwnedCards\Service as OwnedCardManager;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

class MassSimReader
{
    /** @var  EntityManager */
    private $em;

    /** @var OwnedCardManager */
    private $ownedCardManager;

    use LoggerAwareTrait;

    public function __construct(EntityManager $entityManager, OwnedCardManager $manager)
    {
        $this->em = $entityManager;
        $this->ownedCardManager = $manager;
        $this->logger = new NullLogger();
    }

    public function getPlayerCardMap($filePath)
    {
        $content = $this->getContentArray($filePath);
        $map = array();
        $_tmpMap = [];
        $currentPlayerName = "";
        foreach ($content as $line) {
            $match = [];
            preg_match('/MemberDeck(\d+)=/', $line, $match);
            if (count($match) == 2) {
                $_tmpMap[$match[1]] = $this->transformOwnedCards($line);
            }

            preg_match('/echo "member name (.+)@/', $line, $match);
            if (count($match) == 2) {
                $currentPlayerName = $match[1];
                if (!isset($map[$currentPlayerName])) {
                    $map[$currentPlayerName] = [];
                }
            }

            if (strpos($line, "./tuo") !== false) {
                $map[$currentPlayerName] = array_merge($map[$currentPlayerName], $this->transformDeckCards($line));
            }

            preg_match('/-o="\$MemberDeck(\d+)"/', $line, $match);
            if (count($match) == 2) {
                $playerId = $match[1];
                $map[$currentPlayerName] = array_merge($map[$currentPlayerName], $_tmpMap[$playerId]);
            }
        }

        return $map;
    }

    public function savePlayerCardMap($map)
    {
        $result = [];

        foreach ($map as $playerName => $cardArray) {
            $player = $this->findPlayerOrCreate($playerName);
            $this->logger->debug("Trying to persist ".count($cardArray). " cards for Player ".$player->getName());
            $result[$player->getName()] = $this->ownedCardManager->transformArrayToModels($player, $cardArray);
            $this->ownedCardManager->removeOldOwnedCardsForPlayer($player);
            foreach ($result[$player->getName()] as $card) {
                $this->em->persist($card);
            }
            $this->em->flush();
            $this->logger->debug("persisted ".count($result[$playerName]). " cards for Player ".$playerName);
        }
        $this->em->flush();
        return $result;
    }

    private function findPlayerOrCreate($playerName)
    {
        $playerRepo = $this->em->getRepository('LokiTuoResultBundle:Player');
        $player = $playerRepo->findOneBy(['name' => $playerName]);
        if (!$player) {
            $this->logger->info("Created Player $playerName because no Player was found.");
            $player = new Player();
            $player->setName($playerName);
            $this->em->persist($player);
        }
        return $player;
    }

    /**
     * @param $filePath
     * @return array
     */
    private function getContentArray($filePath)
    {
        //TODO Check if file exists
        return explode("\n", file_get_contents($filePath));
    }


    private function transformOwnedCards($line)
    {
        $regEx = '/MemberDeck\d+="(.+)"/';
        $inDeck = false;
        return $this->transformWithRegEx($line, $regEx, $inDeck);
    }

    private function transformDeckCards($line)
    {
        $regEx = '/\.\/tuo "(.*)" "/';
        $inDeck = true;
        return $this->transformWithRegEx($line, $regEx, $inDeck);
    }

    private function transformWithRegEx($line, $regEx, $inDeck = false)
    {
        $owned = [];
        $matches = [];
        preg_match($regEx, $line, $matches);
        $cards = explode(",", $matches[1]);
        foreach ($cards as $card) {
            $owned[] = $this->ownedCardManager->transformCardString(trim($card), $inDeck);
        }
        return $owned;
    }
}
