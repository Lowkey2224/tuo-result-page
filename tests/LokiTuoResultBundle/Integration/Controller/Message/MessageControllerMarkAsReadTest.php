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
        $id = 1;
        $url = sprintf("/message/%d/read", $id);
        $client->request("GET", $url);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $repo = $this->container->get('doctrine')->getRepository('LokiTuoResultBundle:BattleLog');
        /** @var BattleLog $blog */
        $blog = $repo->find($id);
        $this->assertEquals(BattleLog::STATUS_READ, $blog->getStatus());
    }
}
