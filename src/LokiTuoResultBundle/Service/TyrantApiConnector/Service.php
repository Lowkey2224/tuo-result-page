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

    const STATEGY_MOST_GOLD = 1;
    const STATEGY_LEAST_ELO = 2;

    public function __construct(LoggerInterface $logger, string $hashSalt, string $signatureSalt)
    {
        $this->logger = $logger;

        $this->connector = new Connector($logger, $hashSalt, $signatureSalt);
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

    public function test(Player $player, string $message, $options = [])
    {
        return $this->connector->test($player, $message, $options);
    }

    public function claimBonus(Player $player)
    {
        $result = $this->connector->test($player, Connector::CLAIM_BONUS, []);
        if (!$result->result) {
            return [
                'result' => $result->result,
                'message' => $result->result_message[0],
                'daily_time' => $result->daily_bonus_time,
            ];
        }
        return ['result' => $result->result, 'tuId' => $result->bonus_result->bonus->card];
    }

    public function doSingleBattle(Player $player, int $enemySelectionStrategy = self::STATEGY_MOST_GOLD)
    {
        $this->logger->info("Selecting Target");
        $result = $this->connector->test($player, Connector::GET_HUNTING_TARGETS, []);
        $enemyId = $this->selectEnemy($result, $enemySelectionStrategy);
        $this->logger->info("Starting Battle");
        $result = $this->connector->test($player, Connector::START_BATTLE, ['target_user_id' => $enemyId]);

        $battleId = $result->battle_data->battle_id;
        $this->logger->info("Finishing Battle on Auto");
        $result = $this->connector->test($player, Connector::PLAY_CARD, [
            'battle_id' => $battleId,
            'skip' => 1,
            'card_uid' => (int)rand(1, 3),
            'data_usage' => 78,
        ]);
        $gold = $result->battle_data->rewards[0]->gold;
        $rating = $result->battle_data->rewards[0]->rating_change;
        return ["gold" => $gold, "rating" => $rating, "stamina" => $result->user_data->stamina];
    }

    public function battleAllBattles(Player $player, int $enemySelectionStrategy = self::STATEGY_MOST_GOLD)
    {
        $result = $this->connector->test($player, Connector::GET_HUNTING_TARGETS, []);
        $stamina = $result->user_data->stamina;
        $this->logger->info(sprintf("Doing %d battles for Player %s", $stamina, $player->getName()));
        $result = [];
        for (; $stamina > 0; $stamina--) {
            $result[] = $this->doSingleBattle($player, $enemySelectionStrategy);
            sleep(3);
        }
        return $result;
    }

    private function selectEnemy($data, int $strategy)
    {
        if (!$strategy) {
            throw new \Exception();
        }
        $maxGold = 0;
        $targetId = 0;
        foreach ($data->hunting_targets as $id => $target) {
            if ($target->gold > $maxGold) {
                $maxGold = $target->gold;
                $targetId = $id;
            }
        }
        return $targetId;
    }
}
