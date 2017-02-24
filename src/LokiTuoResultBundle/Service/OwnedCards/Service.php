<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 07.10.16
 * Time: 14:28
 */

namespace LokiTuoResultBundle\Service\OwnedCards;

use Doctrine\ORM\EntityManager;
use Illuminate\Support\Collection;
use LokiTuoResultBundle\Entity\OwnedCard;
use LokiTuoResultBundle\Entity\Player;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

class Service
{

    /** @var  EntityManager */
    private $em;

    use LoggerAwareTrait;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
        $this->logger = new NullLogger();
    }

    public function transformCardString($card, $inDeck = false)
    {
        $amount = 1;
        $level = null;
        $match = [];
        $name = '';
        preg_match('/.+\((\d+)\)/', $card, $match);
        if (count($match) == 2) {
            $amount = $match[1];
        }
        $match = [];

        preg_match('/.+-(\d)/', $card, $match);
        if (count($match) == 2) {
            $level = $match[1];
        }
        $match = [];
        $inDeck = ($inDeck)?$amount:0;
        preg_match('/([a-zA-Z \- \. \' \d]+)\b/', $card, $match);
        if (count($match) >= 2) {
            $match2 =[];
            preg_match('/(.*)\-\d/', $match[1], $match2);

            $name = count($match2) == 2 ? $match2[1] : $match[1];
        }
        return ['amount' => $amount, 'level' => $level, 'name' => $name, 'inDeck' => $inDeck];
    }

    public function persistOwnedCards(array $ownedCards)
    {
        foreach ($ownedCards as $ownedCard) {
            if ($ownedCard instanceof OwnedCard) {
                $this->em->persist($ownedCard);
            }
        }
        $this->em->flush();
    }

    /**
     * @param Player $player The Player which the Cards belong to.
     * @param $cardArray array in the form [0 => ['name'=> 'CARD_NAME', 'amount'=>'1', 'level'=>null, 'inDeck'=false]]
     * @return OwnedCard[]
     */
    public function transformArrayToModels(Player $player, $cardArray)
    {

        $cardRepo = $this->em->getRepository('LokiTuoResultBundle:Card');
        $result = [];
        foreach ($cardArray as $cardEntry) {
//            $this->removeOldOwnedCardsForPlayer($player);
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
            $oc->setAmountInDeck($cardEntry['inDeck']);
            $this->logger->error("Persisting Card $oc");
            $result[] = $oc;
        }

        return $result;
    }

    /**
     * Deletes all Owned Cards for a given Player
     * @param Player $player
     * @return int The number of deleted Cards
     */
    public function removeOldOwnedCardsForPlayer(Player $player)
    {
        $ownderCardRepo = $this->em->getRepository('LokiTuoResultBundle:OwnedCard');
        $oldCards = $ownderCardRepo->findBy(['player' => $player]);
        foreach ($oldCards as $ownedCard) {
            $this->em->remove($ownedCard);
        }
        $this->em->flush();
        return count($oldCards);
    }

    public function deckToSpreadsheetFormat(Player $player)
    {
        $ocs = new Collection($player->getOwnedCards());
        $deck = $ocs->filter(function (OwnedCard $oc) {
            return $oc->getAmountInDeck()>0;
        });
        return $deck->map(", ");
    }

    public function ownedCardToSpreadsheetFormat(Player $player)
    {
        $ocs = new Collection($player->getOwnedCards());
        return $ocs->map("\n");
    }
}
