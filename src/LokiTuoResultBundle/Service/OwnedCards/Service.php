<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 07.10.16
 * Time: 14:28.
 */

namespace App\LokiTuoResultBundle\Service\OwnedCards;

use App\LokiTuoResultBundle\Entity\Card;
use App\LokiTuoResultBundle\Entity\CardLevel;
use App\LokiTuoResultBundle\Entity\OwnedCard;
use App\LokiTuoResultBundle\Entity\Player;
use Doctrine\ORM\EntityManager;
use Illuminate\Support\Collection;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

class Service
{
    /** @var EntityManager */
    private $em;

    use LoggerAwareTrait;

    /**
     * @var Collection
     */
    private $levels;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
        $this->logger = new NullLogger();
        $this->levels = null;
    }

    /**
     * @param $card
     * @param bool $inDeck
     * @return array
     */
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
        $nameAddition = "";
        preg_match('/.+-(\d+)/', $card, $match);
        if (count($match) == 2) {
            if ($match[1] <= 6) {
                $level = $match[1];
            } else {

                $nameAddition = "-" . $match[1];
            }
        }
        $match = [];
        $inDeck = ($inDeck) ? $amount : 0;
        preg_match('/([a-zA-Z \- \. \' \d]+)\b/', $card, $match);
        if (count($match) >= 2) {
            $match2 = [];
            preg_match('/(.*)\-\d/', $match[1], $match2);

            $name = count($match2) == 2 ? $match2[1] : $match[1];
            $name .= $nameAddition;
        }

        return ['amount' => (int)$amount, 'level' => $level ? (int)$level : null, 'name' => $name, 'inDeck' => $inDeck];
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
     * @param Player $player the Player which the Cards belong to
     * @param $cardArray array in the form [0 => ['name'=> 'CARD_NAME', 'amount'=>'1', 'level'=>null, 'inDeck'=false]]
     *
     * @return OwnedCard[]
     */
    public function transformArrayToModels(Player $player, $cardArray)
    {
        $cardRepo = $this->em->getRepository('LokiTuoResultBundle:Card');
        $result = [];
        foreach ($cardArray as $cardEntry) {
            $card = $cardRepo->findOneBy(['name' => $cardEntry['name']]);
            if (!$card instanceof Card) {
                $this->logger->notice('No Card found for name ' . $cardEntry['name']);
                continue;
            }
            $oc = new OwnedCard();
            $selectedLevel = $card->getLevel($cardEntry['level']);

            if (!$selectedLevel instanceof CardLevel) {
                $this->logger->notice(sprintf('No corresponding Level %d found for Card %d', $cardEntry['level'],
                    $cardEntry['name']));
                continue;
            }

            $oc->setCard($selectedLevel);
            $oc->setAmount($cardEntry['amount']);
            $oc->setPlayer($player);
            $oc->setAmountInDeck($cardEntry['inDeck']);
            $this->logger->debug("Persisting Card $oc");
            $result[] = $oc;
        }

        return $result;
    }

    /**
     * Deletes all Owned Cards for a given Player.
     *
     * @param Player $player
     *
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
            return $oc->getAmountInDeck() > 0;
        });

        return $deck->map(', ');
    }

    public function ownedCardToSpreadsheetFormat(Player $player)
    {
        $ocs = new Collection($player->getOwnedCards());

        return $ocs->map("\n");
    }

    public function persistOwnedCardsByTuoId(array $ids, Player $player)
    {
        $ocs = $this->getOwnedCardsByTuoIds($ids, $player);
        foreach ($ocs as $ownedCard) {
            $this->em->persist($ownedCard);
        }
        $player->setUpdatedAtValue();
        $this->em->persist($player);
        $this->em->flush();
        return $ocs;
    }

    /**
     * @param integer[] $tuIds
     * @param Player $player
     * @return OwnedCard[] array
     */
    private function getOwnedCardsByTuoIds(array $tuIds, Player $player)
    {
        if (!$this->levels) {
            $this->logger->info("Fething level info");
            $this->prepare();
        }
        $ownedCards = [];
        foreach ($tuIds as $tuId => $amounts) {
            if (!$this->levels->get($tuId)) {
                $this->logger->warning("Unknown Card was tried to persist with ID: " . $tuId);
                continue;
            }
            $amount = $amounts['owned'];
            $amountInDeck = isset($amounts['used']) ? $amounts['used'] : 0;
            $oc = new OwnedCard();

            $oc->setCard($this->levels->get($tuId));
            $oc->setAmount($amount);
            $oc->setPlayer($player);
            $oc->setAmountInDeck($amountInDeck);
            if ($oc->getCard()) {
                $ownedCards[$tuId] = $oc;
            }
        }
        return $ownedCards;
    }

    /**
     * Fetches Carddata and filles $this->levels
     */
    private function prepare()
    {

        $cardRepo = $this->em->getRepository('LokiTuoResultBundle:Card');
        $cards = $cardRepo->findAllWithLevels();
        $cards = new Collection($cards);
        $levels = $cards->map(function (Card $c) {
            return $c->getLevels();
        });
        $levelsFlat = new Collection();
        foreach ($levels as $levelsForOneCard) {
            /** @var CardLevel $level */
            foreach ($levelsForOneCard as $level) {
                $levelsFlat[$level->getTuoId()] = $level;
            }
        }
        $this->levels = $levelsFlat;
        unset($levelsFlat, $levels, $cards);
    }
}
