<?php


namespace LokiTuoResultBundle\Service\TyrantApiConnector;


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

    public function getDecks()
    {

//        $userId = 3565325;
//        $userPassword = "d9810b1022d7984267a0d77c9ede08bc";
//        $userName = "kentasaurus";
//        $targerUserId = 4401099;
//        $kongId = 15485404;
//        $actions = [
//            'getProfileData',
//            'init',
//        ];
        //LokiMcFly
        $userPassword = "b2541a76c4739991be8462ded7816c5b";
        $userId = 10140147;
        $synCode = "03f475cb8d8840be77787acc354d42ddaa51da44295941c27eacb38bb894e4ea";
        $userName = "LokiMcFly";
        $kongId = 5837616;
        $kongToken = "ed2599b605e3556b4d8f7471078b7c0e3d41c0b435296f3d4b00dc7ec9515218";



//
//        $members = $this->connector->getMembers($userId, $userName, $userPassword, $userId, $kongId, $synCode, $kongToken);
//        $members = $members->faction->members;
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

                $cardIds[$id] = isset($cardIds[$id]) ? $cardIds[$id] + $value->num_owned : (int)$value->num_owned;
            }
            if ($value->num_used > 0) {
                $this->logger->info(sprintf("My Deck has the Following Card"));
                $countDeck++;
                $cardIds[$id] = isset($cardIds[$id]) ? $cardIds[$id] + $value->num_used : (int)$value->num_used;
            }
            if ($value->num_owned == 0) {
                $this->logger->info(sprintf("Previosly Owned"));
            }
        }
        $deckCards = [];
        foreach ($decks as $deck) {
            $cardIds[$deck->dominion_id] = 1;
            $cardIds[$deck->commander_id] = 1;
            foreach ($deck->cards as $id => $amount) {
                $val = isset($deckCards[$id]) ? $deckCards[$id] : 0;
                $newVal = $amount;
                $deckCards[$id] = max($val, $newVal);
            }
        }
        foreach ($deckCards as $id => $amount) {
            $val = isset($cardIds[$id]) ? $cardIds[$id] : 0;
            $cardIds[$id] = $val + $amount;

        }

//        foreach ($members as $id => $value) {
//            $name = $value->name . '';
//
//            $main[] = ['name'];
//            $main[] = [$name];
//
//
//            $server_output = $this->connector->getCards($id);
//
//            $content = json_decode($server_output);
//
//            foreach (["deck", "defense_deck"] as $deckType) {
//                if ($content->player_info->$deckType) {
//                    $main[] = $this->connector->getDeck($content->player_info, $deckType);
//                }
//            }
//
//            flush();
//        }
        return $cardIds;
    }
}