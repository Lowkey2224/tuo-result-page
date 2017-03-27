<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 12.01.17
 * Time: 11:51.
 */

namespace LokiTuoResultBundle\Service\VpcSImulation;

use Buzz\Browser;
use LokiTuoResultBundle\Service\Simulation\Simulation;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

class Service
{
    use LoggerAwareTrait;

    /** @var Browser */
    private $browser;

    /** @var string */
    private $url;

    /**
     * Service constructor.
     *
     * @param Browser $browser BuzzBrowser to send http requests
     * @param string  $vpc_url the url (without http) of the vpc running the
     *                         https://github.com/benprew/tuo-queue/blob/master/README.md
     */
    public function __construct(Browser $browser, $vpc_url)
    {
        $this->browser = $browser;
        $this->url     = $vpc_url;
        $this->setLogger(new NullLogger());
    }

    public function postSimulation(Simulation $simulation)
    {
        $url     = 'http://'.$this->url.'/job/create';
        $headers = [];
        $player  = $simulation->getPlayers()[0];
        $content = $this->simulationToArray($simulation);
        $content = implode('&', $content);

        $response = $this->browser->post($url, $headers, $content);

        return true;
    }

    public function post2(Simulation $simulation)
    {
        $url     = 'http://'.$this->url.'/job/create';
        $arr     = $this->simulationToArray($simulation);
        $content = $this->arr2Body($arr);
        $result  = $this->sendCurl($arr, $content, $url);
        $res     = $this->processCurlResult($result);

        return $res;
    }

    private function simulationToArray(Simulation $simulation)
    {
        $player              = $simulation->getPlayers()[0];
        $content             = [];
        $content['username'] = $player->getName();
        $content['deck']     = implode(',', $player->getDeck()->toArray());
        $content['deck']     = str_replace('\'', '', $content['deck']);

        $content['your_inventory'] = implode(',', $player->getOwnedCards()->toArray());
        $content['your_inventory'] = str_replace('\'', '', $content['your_inventory']);
        $content['your_structs']   = implode(',', $simulation->getStructures());
        if ($simulation->isOrdered()) {
            $content['ordered'] = $simulation->isOrdered();
        }
        if ($simulation->isSurge()) {
            $content['mode'] = $simulation->isSurge();
        }
        $content['enemy_deck']    = $simulation->getMissions()[0];
        $content['enemy_structs'] = implode(',', $simulation->getEnemyStructures());
        $content['command']       = $simulation->getSimType();
//        $content['cmd_count'] = $simulation->getIterations();
        $content['cmd_count'] =  1;
        $content['fund']      = 0;
        if ($simulation->getBackgroundEffect()) {
            $content['bge'] = $simulation->getBackgroundEffect()->getName();
        }

        return $content;
    }

    private function arr2Body(array $simulation)
    {
        $fields_string = '';

        foreach ($simulation as $key => $value) {
            $value = str_replace("''", '', $value);
            $fields_string .= $key.'='.$value.'&';
        }
        rtrim($fields_string, '&');

        return $fields_string;
    }

    private function sendCurl($arr, $content, $url)
    {
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, count($arr));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);

        //execute post
        $result = curl_exec($ch);

        //close connection
        curl_close($ch);

        return $result;
    }

    private function processCurlResult($result)
    {
        $results = explode("\n", $result);
        $status  = $this->getStatusCode($results);
        $header  = ['status' => $status];
        foreach ($results as $statusLine) {
            $tmp = explode(': ', $statusLine);
            if (count($tmp) == 2) {
                list($key, $value)        = $tmp;
                $header[strtolower($key)] = $value;
            }
        }
        $ret['status'] = $header['status'];
        $id            = isset($header['location']) ? explode('/job/', $header['location']) : null;
        $ret['id']     = intval($id[1]);

        return $ret;
    }

    private function getStatusCode($arr)
    {
        $status = null;
        foreach ($arr as $line) {
            if (0 === strpos($line, 'HTTP/1.1')) {
                $exploded = explode(' ', $line);

                $status = $exploded[1];
            }
        }

        return $status;
    }
}
