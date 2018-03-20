<?php


namespace App\LokiTuoResultBundle\Service\TyrantApiConnector;


use App\LokiTuoResultBundle\Entity\Player;
use Psr\Log\LoggerInterface;

class Connector
{
    const GET_INVENTORY = "init";
    const GET_DECKS = "getProfileData";
    const GET_MEMBERS = "updateFaction";
    const GET_HUNTING_TARGETS = "getHuntingTargets";
    const START_BATTLE = "startHuntingBattle";
    const PLAY_CARD = "playCard";
    const CLAIM_BONUS = "useDailyBonus";

    /**
     * @var string
     */
    private $adapter;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $hashSalt;

    /**
     * @var string
     */
    private $signatureSalt;

    public function __construct(LoggerInterface $logger, string $hashSalt, string $signatureSalt)
    {
        $this->logger = $logger;
        $this->adapter = "HTTP_Request2_Adapter_Curl";
        $this->hashSalt = $hashSalt;
        $this->signatureSalt = $signatureSalt;
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

        $time = time();
        //TODO make this editable,
        $version = "77.02";
        $body = [
            'password' => $player->getKongCredentials()->getKongPassword(),
            'client_version' => $version,

            'user_id' => $player->getKongCredentials()->getTuUserId(),
            'timestamp' => $time,
            'client_time' => $time,
            'syncode' => $player->getKongCredentials()->getSynCode(),
            'kong_id' => $player->getKongCredentials()->getKongId(),
            'kong_token' => $player->getKongCredentials()->getKongToken(),
            'kong_name' => $player->getKongCredentials()->getKongUserName(),
            'hash' => md5($this->hashSalt . $player->getKongCredentials()->getTuUserId() . $time),
            'client_signature' => md5($time . $player->getKongCredentials()->getKongPassword() . $this->signatureSalt),
            'unity' => "Unity5_4_2",
            'os_version' => "Mac+OS+X+10.12",
            'platform' => 'Web',
            'device_type' => 'Firefox+56.0',
            'data_usage' => 0, //TODO calc this According to Here'sJohnny! "data_usage" = Kilobytes Downloaded
            'api_stat_name' => $player->getLastApiMessage(),
            'api_stat_time' => $time - $player->getLastApiTime(),
        ];
        $player->setLastApiMessage($method);
        $player->setLastApiTime($time);
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
        //TODO dispatch Event, if $result->version != $this->version
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