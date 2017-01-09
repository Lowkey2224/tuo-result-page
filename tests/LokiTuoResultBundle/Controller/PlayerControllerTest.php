<?php

namespace LokiTuoResultBundle\Tests\Controller;

use LokiTuoResultBundle\Controller\PlayerController;

class PlayerControllerTest extends \AbstractControllerTest
{

    /**
     * @covers PlayerController::listAllPlayersAction()
     */
    public function testListAllPlayersAction()
    {
        $client = $this->loginAs();

        $crawler = $client->request('GET', '/player/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(1,
            $crawler->filterXPath('.//tr/td[normalize-space()="loki"]')->count()
        );
    }



    public function testShowcardsforplayer()
    {
        $client = $this->loginAs();

        $repo = $this->container->get('doctrine')->getRepository('LokiTuoResultBundle:Player');
        $player = $repo->findOneBy(['name' => 'loki']);
        $crawler = $client->request('GET', '/player/'.$player->getId().'/cards');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $xpaths = [
            './/td[normalize-space()="Malika"]',
            './/td[normalize-space()="Stonewall Garrison"]',
            './/td[normalize-space()="Inheritor of Hope"]',
        ];
        foreach ($xpaths as $xpath)
        {
            $this->assertEquals(2, $crawler->filterXPath($xpath)->count());
        }
    }

    /**
     * @covers PlayerController::showResultsForPlayerAction()
     */
    public function testShowResultsForPlayerAction()
    {
        $client = $this->loginAs();
        $repo = $this->container->get('doctrine')->getRepository('LokiTuoResultBundle:Player');
        $player = $repo->findOneBy(['name' => 'loki']);
        $crawler = $client->request('GET', '/player/'.$player->getId().'/results');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $xpaths = [
            './/a/b[normalize-space()="iron mutant-5"]',
            './/a/b[normalize-space()="[CTP] loki"]',
            './/div[@class="panel-heading" and contains(.,"80.8")]',
        ];
        foreach ($xpaths as $xpath)
        {
            $this->assertGreaterThan(0, $crawler->filterXPath($xpath)->count());
        }
    }
}
