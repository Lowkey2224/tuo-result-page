<?php


namespace LokiTuoResultBundle\Service\TyrantApiConnector;


use LokiTuoResultBundle\Entity\Player;
use Psr\Log\LoggerInterface;

class Connector
{
    const GET_INVENTORY = "init";
    const GET_DECKS = "getProfileData";
    const GET_MEMBERS = "updateFaction";

    /**
     * @var string
     */
    private $adapter;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->adapter = "HTTP_Request2_Adapter_Curl";
    }

    /**
     * @param string $adapter
     * @return Connector
     */
    public function setAdapter($adapter)
    {
        $this->adapter = $adapter;
        return $this;
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
     * @return object|null The Resultstring or null
     */
    private function apiCall($method, array $options)
    {
        $url = sprintf('https://mobile.tyrantonline.com/api.php?message=%s&user_id=%d', $method, $options['user_id']);
        $this->logger->debug(sprintf("Using URL %s", $url));

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
        $req = new \HTTP_Request2($url, \HTTP_Request2::METHOD_POST, []);
        $req->setBody($bodyStr);
        $req->setAdapter($this->adapter);
        $response = $req->send();
        if ($response->getStatus() == 200) {
            return json_decode($response->getBody());
        } else {
            $this->logger->warning("Error from TU API: Status: " . $response->getReasonPhrase());
            $this->logger->warning("Body was " . $response->getBody());
        }
        return null;
    }

    /**
     * @param Player $player
     * @return array
     */
    public function getInventory(Player $player)
    {
        $options = [
            'password' => $player->getKongCredentials()->getKongPassword(),
            'target_user_id' => $player->getKongCredentials()->getTuUserId(),
            'user_id' => $player->getKongCredentials()->getTuUserId(),
            'syncode' => $player->getKongCredentials()->getSynCode(),
            'kongId' => $player->getKongCredentials()->getKongId(),
            'user_name' => $player->getKongCredentials()->getKongUserName(),
            'kong_token' => $player->getKongCredentials()->getKongToken(),
        ];
        $result = $this->apiCall(self::GET_INVENTORY, $options);
        if (isset($result_bubyack_data)) {
            $this->logger->info("Buyback exists");
        }

        return [
            isset($result->user_cards) ? $result->user_cards : [],
            isset($result->user_decks) ? $result->user_decks : [],
        ];
    }
}