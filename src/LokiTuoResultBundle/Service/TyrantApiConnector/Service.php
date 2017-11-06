<?php


namespace LokiTuoResultBundle\Service\TyrantApiConnector;


use LokiTuoResultBundle\Entity\Player;
use Psr\Log\LoggerInterface;

class Service
{
    /** @var LoggerInterface */
    private $logger;

    /** @var Connector */
    private $connector;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;

        $this->connector = new Connector($logger);
    }

    /**
     * Calls the linked method internally
     * @param string $adapter
     * @see \HTTP_Request2::setAdapter()
     */
    public function setRequestAdapter($adapter)
    {
        $this->connector->setAdapter($adapter);
    }

    public function getInventoryAndDeck(Player $player)
    {
        if (!$player->hasKongCredentials()) {
            return [];
        }
        $this->logger->info("Fetching Data for Player " . $player->getName());
        list($cards, $decks) = $this->connector->getInventory($player);
        $cardIds = $this->handleCards($cards);
        $cardIds = $this->handleDecks($cardIds, $decks);

        $this->logger->info(sprintf("Found in total %d different Cards", count($cardIds)));
        return $cardIds;
    }

    private function handleCards($cards = null)
    {
        $this->logger->info(sprintf("Found %d Cards", count($cards)));
        $countOwned = 0;
        $countDeck = 0;
        $countKnown = 0;
        $cardIds = [];
        foreach ($cards as $id => $value) {
            $countKnown++;
            if ($value->num_owned > 0) {
                $countOwned++;
                $cardIds[$id]["owned"] = isset($cardIds[$id]) ? $cardIds[$id]["owned"] + $value->num_owned : (int)$value->num_owned;
            }
            if ($value->num_used > 0) {
                $countDeck++;
                $cardIds[$id]["owned"] = isset($cardIds[$id]) ? $cardIds[$id]["owned"] + $value->num_used : (int)$value->num_used;
            }
        }

        return $cardIds;
    }

    private function handleDecks(array $cardIds, $decks = null)
    {
        $this->logger->info(sprintf("Found %d Decks", count($decks)));
        foreach ($decks as $deck) {
            if (count($deck->cards) > 0) {

                $cardIds[$deck->dominion_id]["used"] = 1;
                $cardIds[$deck->commander_id]["used"] = 1;
                foreach ($deck->cards as $id => $amount) {
                    $cardIds[$id]["used"] = isset($cardIds[$id]["used"]) ? $cardIds[$id]["used"] + $amount : (int)$amount;
                }
                break;
            }
        }
        return $cardIds;
    }
}
