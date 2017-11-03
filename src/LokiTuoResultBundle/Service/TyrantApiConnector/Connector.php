<?php


namespace LokiTuoResultBundle\Service\TyrantApiConnector;


use LokiTuoResultBundle\Entity\Player;
use Psr\Log\LoggerInterface;

class Connector
{
    const GET_INVENTORY = "init";
    const GET_DECKS = "getProfileData";
    const GET_MEMBERS = "updateFaction";
    const GET_HUNTING_TARGETS = "getHuntingTargets";
    const START_BATTLE = "startHuntingBattle";
    const PLAY_CARD = "playCard";

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
     * Makes an API Call to the tyrantonline API
     * @param string $method The method you want to call
     * @param string[] $options options TODO need to be validated
     * @return object|null The Resultstring or null
     */
    private function apiCall($method, Player $player, array $options = [])
    {
        $url = sprintf('https://mobile.tyrantonline.com/api.php?message=%s&user_id=%d', $method,
            $player->getKongCredentials()->getTuUserId());
        $this->logger->info(sprintf("Using URL %s", $url));
        $salt = 'TR&Q$K';
        $time = time();
        $body = [
            'password' => $player->getKongCredentials()->getKongPassword(),
            'client_version' => 77, //TODO make this editable,
            'user_id' => $player->getKongCredentials()->getTuUserId(),
            'timestamp' => $time,
            'client_time' => $time,
            'syncode' => $player->getKongCredentials()->getSynCode(),
            'kong_id' => $player->getKongCredentials()->getKongId(),
            'kong_token' => $player->getKongCredentials()->getKongToken(),
            'kong_name' => $player->getKongCredentials()->getKongUserName(),
            'hash' => md5($salt . $player->getKongCredentials()->getTuUserId() . $time),
            'client_signature' => md5($time . $player->getKongCredentials()->getKongPassword() . 'emJwaVK0HrTxVjIONHYH'),
            'unity' => "Unity5_4_2",
            'os_version' => "Mac+OS+X+10.12",
            'platform' => 'Web',
            'device_type' => 'Firefox+56.0',
            'data_usage' => 0, //TODO calc this According to Here'sJohnny! "data_usage" = Kilobytes Downloaded
            'api_stat_name' => 0, //TODO Last API Call message, this needs to be saved inside the Player
            'api_stat_time' => 0, //TODO Diff to Last API Call timestamp,  this needs to be saved inside the Player
        ];
        $body = array_merge($body, $options);
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

        $result = $this->apiCall(self::GET_INVENTORY, $player);
        if (isset($result_bubyack_data)) {
            $this->logger->info("Buyback exists");
        }

        return [
            isset($result->user_cards) ? $result->user_cards : [],
            isset($result->user_decks) ? $result->user_decks : [],
        ];
    }

    /**
     * Sends any message
     * @param Player $player Player
     * @param string $message the message you send
     * @param array $options additional body params
     * @return null|object
     */
    public function test(Player $player, string $message, $options = [])
    {
        return $this->apiCall($message, $player, $options);
    }
}