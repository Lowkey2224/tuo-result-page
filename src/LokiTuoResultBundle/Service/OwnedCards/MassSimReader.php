<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 16.08.16
 * Time: 18:49
 */

namespace LokiTuoResultBundle\Service\OwnedCards;

use Doctrine\ORM\EntityManager;
use LokiTuoResultBundle\Entity\Card;
use LokiTuoResultBundle\Entity\OwnedCard;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class MassSimReader
{
    /** @var  EntityManager */
    private $em;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
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
            $this->logger->debug("Trying to persist ".count($cardArray). " cards for Player $playerName");
            $result[$playerName] = $this->saveCardsForPlayer($playerName, $cardArray);
            foreach ($result[$playerName] as $card) {
                $this->em->persist($card);
            }
            $this->em->flush();
            $this->logger->debug("persisted ".count($result[$playerName]). " cards for Player $playerName");
        }
        $this->em->flush();
        return $result;
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

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param $player
     * @return int
     */
    private function removeOldOwnedCardsForPlayer($player)
    {
        $ownderCardRepo = $this->em->getRepository('LokiTuoResultBundle:OwnedCard');
        $oldCards = $ownderCardRepo->findBy(['player' => $player]);
        foreach ($oldCards as $ownedCard) {
            $this->em->remove($ownedCard);
        }
        $this->em->flush();
        return count($oldCards);
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
            $owned[] = $this->transformCardString(trim($card), $inDeck);
        }
        return $owned;
    }

    public function transformCardString($card, $inDeck = false)
    {
        $amount = 1;
        $level = null;
        $match = [];
        $name = '';
        preg_match('/.+ \((\d+)\)/', $card, $match);
        if (count($match) == 2) {
            $amount = $match[1];
        }
        $match = [];

        preg_match('/.+-(\d)/', $card, $match);
        if (count($match) == 2) {
            $level = $match[1];
        }
        $match = [];
        preg_match('/([a-zA-Z \- \. \' \d]+)\b/', $card, $match);
        if (count($match) >= 2) {
            $match2 =[];
            preg_match('/(.*)\-\d/', $match[1], $match2);

            $name = count($match2) == 2 ? $match2[1] : $match[1];
        }
        return ['amount' => $amount, 'level' => $level, 'name' => $name, 'inDeck' => $inDeck];
    }

    private function saveCardsForPlayer($playerName, $cardArray)
    {

        $playerRepo = $this->em->getRepository('LokiTuoResultBundle:Player');
        $cardRepo = $this->em->getRepository('LokiTuoResultBundle:Card');
        $player = $playerRepo->findOneBy(['name' => $playerName]);
        if (!$player) {
            $this->logger->notice("No player found for name ".$playerName);
            return [];
        }
        $result = [];
        foreach ($cardArray as $cardEntry) {
            $this->removeOldOwnedCardsForPlayer($player);
            $card = $cardRepo->findOneBy(['name' => $cardEntry['name']]);
            if (!$card) {
                $this->logger->notice("No Card found for name " . $cardEntry['name']);
                continue;
            }
            $oc = new OwnedCard();
            $oc->setCard($card);
            $oc->setAmount($cardEntry['amount']);
            $oc->setLevel($cardEntry['level']);
            $oc->setPlayer($player);
            $oc->setInCurrentDeck($cardEntry['inDeck']);
            $result[] = $oc;
        }
//        $ac = new ArrayCollection($result);
//        $player->setOwnedCards($ac);
//        $this->em->persist($player);

        return $result;
    }
}
