<?php

namespace LokiTuoResultBundle\Integration\Controller\Message;

use LokiTuoResultBundle\Entity\BattleLog;
use LokiTuoResultBundle\Entity\Player;
use Tests\LokiTuoResultBundle\Integration\Controller\AbstractControllerTest;

class MessageControllerMarkAsReadTest extends AbstractControllerTest
{
    public function testMarkAsRead()
    {
        $player = new Player();
        $bl = new BattleLog();
        $bl->setStatus(BattleLog::STATUS_UNREAD)
            ->setBattles(1)
            ->setRating(1)
            ->setGold(1)
            ->setWon(1)
            ->setPlayer($player);
        $client = $this->loginAs();

//        $crawler = $client->request("GET", sprintf("/message/%d/read", $battleLog->getId()));
        $crawler = $client->request("GET", "/message/1/read");
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
