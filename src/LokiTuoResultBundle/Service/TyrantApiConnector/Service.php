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

    public function getInventoryAndDeck(Player $player)
    {


        if (!$player->hasKongCredentials()) {
            return [];
        }
        $userPassword = $player->getKongPassword();
        $userId = $player->getTuUserId();
        $synCode = $player->getSynCode();
        $userName = $player->getKongUserName();
        $kongId = $player->getKongId();
        $kongToken = $player->getKongToken();

        list($cards, $decks) = $this->connector->getInventory($userId, $userName, $userPassword, $userId, $kongId,
            $synCode, $kongToken);
        $cardIds = [];
        $countOwned = 0;
        $countDeck = 0;
        $countKnown = 0;
        foreach ($cards as $id => $value) {
            $countKnown++;
            if ($value->num_owned > 0) {
                $this->logger->info(sprintf("My Deck has the Following Card"));
                $countOwned++;

                $cardIds[$id]["owned"] = isset($cardIds[$id]) ? $cardIds[$id]["owned"] + $value->num_owned : (int)$value->num_owned;
            }
            if ($value->num_used > 0) {
                $this->logger->info(sprintf("My Deck has the Following Card"));
                $countDeck++;
                $cardIds[$id]["owned"] = isset($cardIds[$id]) ? $cardIds[$id]["owned"] + $value->num_used : (int)$value->num_used;
            }
            if ($value->num_owned == 0) {
                $this->logger->info(sprintf("Previosly Owned"));
            }
        }
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
