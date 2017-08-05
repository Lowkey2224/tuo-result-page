<?php


namespace LokiTuoResultBundle\Service\TyrantApiConnector;


use Psr\Log\LoggerInterface;

class Connector
{
    const GET_INVENTORY = "init";
    const GET_DECKS = "getProfileData";
    const GET_MEMBERS = "updateFaction";
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Get Members of the Faction for the given Player
     * @param int $userId tyrant userId
     * @param string $userName The shown username
     * @param string $userPassword hashed Password
     * @param string $kongId kongregateId
     * @param string $synCode Syncode to be taken from existing API Requests
     * @param string $kongToken KongTOken to be taken from existing API Requests
     * @return null|string Result
     */
    public function getMembers($userId, $userName, $userPassword, $kongId, $synCode, $kongToken)
    {
        $options = [
            'password' => $userPassword,
            'target_user_id' => $userId,
            'user_id' => $userId,
            'syncode' => $synCode,
            'kongId' => $kongId,
            'user_name' => $userName,
            'kong_token' => $kongToken,
        ];
        $result = $this->apiCall(self::GET_MEMBERS, $options);

        return $result;
    }

    /**
     * Makes an API Call to the tyrantonline API
     * @param string $method The method you want to call
     * @param string[] $options options TODO need to be validated
     * @return string|null The Resultstring or null
     */
    private function apiCall($method, array $options)
    {
        $url = sprintf('https://mobile.tyrantonline.com/api.php?message=%s&user_id=%d', $method, $options['user_id']);

        $body = [
            'password' => $options['password'] . "=Unity4_6_6",
            'client_version' => 61,
            'target_user_id' => $options['target_user_id'],
            'user_id' => $options['user_id'],
            'timestamp' => time(),
            'syncode' => $options['syncode'],
            'kong_id' => $options['kongId'],
            'kong_token' => $options['kong_token'],
            'kong_name' => $options['user_name']
        ];
        $bodyStr = [];
        foreach ($body as $key => $value) {
            $bodyStr[] = $key . "=" . $value;
        }
        $bodyStr = implode("&", $bodyStr);
        if ($curl = curl_init()) {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $bodyStr);  //Post Fields

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $headers = [];
            $headers[] = 'X-Apple-Tz: 0';
            $headers[] = 'X-Apple-Store-Front: 143444,12';
            $headers[] = 'Accept: *\*';
            $headers[] = 'Connection: keep-alive';
            $headers[] = 'Cache-Control: no-cache';
            $headers[] = 'Content-Type:application/json';
            $headers[] = 'Host: mobile.tyrantonline.com';
            $headers[] = 'User-Agent: tyrantmobile/1.26 CFNetwork/672.0.8 Darwin/14.0.0';
            $headers[] = 'X-MicrosoftAjax: Delta=true';
            $headers[] = 'X-Requested-With:XMLHttpRequest';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $server_output = curl_exec($ch);
            curl_close($ch);

            return json_decode($server_output);
        }
        return null;
    }

    public function getInventory($userId, $userName, $userPassword, $targerUserId, $kongId, $synCode, $kongToken)
    {
        $options = [
            'password' => $userPassword,
            'target_user_id' => $targerUserId,
            'user_id' => $userId,
            'syncode' => $synCode,
            'kongId' => $kongId,
            'user_name' => $userName,
            'kong_token' => $kongToken,
        ];
        $result = $this->apiCall(self::GET_INVENTORY, $options);

        return [
            $result->user_cards,
            $result->user_decks,
        ];
    }

    public function getDeck($player, $deckType)
    {
        $deck = [];
        $deck[] = $commander_id = $player->$deckType->commander_id;
        $deck[] = $deck_id = $player->$deckType->deck_id;

        echo "commander_id = " . $commander_id . "\n";
        echo "deck_id = " . $deck_id . "\n";
        echo "_______\n";
        $cards = $player->$deckType->cards;

        foreach ($cards as $cardId) {

            $deck[] = $cardId;
        }
        return $deck;
    }
}