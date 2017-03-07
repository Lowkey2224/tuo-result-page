<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 12.01.17
 * Time: 11:51
 */

namespace LokiTuoResultBundle\Service\VpcSImulation;

use Buzz\Browser;
use LokiTuoResultBundle\Service\Simulation\Simulation;

class Service
{

    /** @var Browser */
    private $browser;

    /** @var  string */
    private $url;

    /**
     * Service constructor.
     * @param Browser $browser BuzzBrowser to send http requests
     * @param string $vpc_url the url (without http) of the vpc running the
     * https://github.com/benprew/tuo-queue/blob/master/README.md
     */
    public function __construct(Browser $browser, $vpc_url)
    {
        $this->browser = $browser;
        $this->url = $vpc_url;
    }

    public function postSimulation(Simulation $simulation)
    {
        $url = "http://" . $this->url . "/job/create";
        $headers = [];
        $content = [];
        $player = $simulation->getPlayers()[0];
        $content['username'] = "username=".$player->getName();
        $content['deck'] = "deck=".implode(',', $player->getDeck()->toArray());
        $content['your_inventory'] = "your_inventory=".implode(",", $player->getOwnedCards()->toArray());
        $content['your_structs'] = "your_structs=".implode(",", $simulation->getStructures());
        if ($simulation->isOrdered()) {
            $content['ordered'] = "ordered=".$simulation->isOrdered();
        }
        if ($simulation->isSurge()) {
            $content['mode'] = "mode=".$simulation->isSurge();
        }
        $content['enemy_deck'] = "enemy_deck=".$simulation->getMissions()[0];
        $content['enemy_structs'] = "enemy_structs=".implode(",", $simulation->getEnemyStructures());
        $content['command'] = "command=".$simulation->getSimType();
//        $content['cmd_count'] = $simulation->getIterations();
        $content['cmd_count'] ="cmd_count=". 1;
        $content['fund'] = "fund=". 0;
        if ($simulation->getBackgroundEffect()) {
            $content['bge'] = "bge=".$simulation->getBackgroundEffect()->getName();
        }
        $content = implode("&", $content);


        $response = $this->browser->post($url, $headers, $content);
        var_dump($url, $response->getHeaders(), $response->getContent());
        return true;
    }
}
