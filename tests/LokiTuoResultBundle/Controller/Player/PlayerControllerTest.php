<?php

namespace LokiTuoResultBundle\Tests\Controller\Player;

use LokiTuoResultBundle\Entity\OwnedCard;
use LokiTuoResultBundle\Entity\Player;
use LokiTuoResultBundle\Tests\Controller\AbstractControllerTest;

class PlayerControllerTest extends AbstractControllerTest
{
    /**
     * covers PlayerController::listAllPlayersAction().
     *
     * @dataProvider playerProvider
     *
     * @param $player
     */
    public function testListAllPlayersAction($player)
    {
        $client = $this->loginAs();

        $crawler = $this->clickLinkName($client, 'Players');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(1,
            $crawler->filterXPath('.//tr/td[normalize-space()="'.$player.'"]')->count()
        );
    }

    /**
     * @param $playerName
     * @dataProvider playerProvider
     */
    public function testShowcardsforplayer($playerName)
    {
        $repo   = $this->container->get('doctrine')->getRepository('LokiTuoResultBundle:Player');
        $player = $repo->findOneBy(['name' => $playerName]);
        $client = $this->loginAs();

        $crawler = $client->request('GET', '/player/'.$player->getId().'/cards');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $deck   = $player->getDeck();
        $xpaths = [];
        /** @var OwnedCard $ownedCard */
        foreach ($deck as $ownedCard) {
            $xpaths[] ='.//td[normalize-space()="'.$ownedCard->getCard()->getName().'"]';
        }

        foreach ($xpaths as $xpath) {
            $this->assertGreaterThan(1, $crawler->filterXPath($xpath)->count());
        }
    }

    /**
     * covers LokiTuoResultBundle\Controller\PlayerController::showResultsForPlayerAction().
     *
     * @dataProvider playerResultProvider
     * @param mixed $playername
     * @param mixed $mission
     * @param mixed $percent
     */
    public function testShowResultsForPlayerAction($playername, $mission, $percent)
    {
        $client = $this->loginAs();
        $repo   = $this->container->get('doctrine')->getRepository('LokiTuoResultBundle:Player');
        /** @var Player $player */
        $player  = $repo->findOneBy(['name' => $playername]);
        $crawler = $client->request('GET', '/player/'.$player->getId().'/results');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $xpaths = [
            './/a/b[normalize-space()="'.$mission.'"]',
            './/a/b[normalize-space()="'.$player->getFullName().'"]',
            './/div[@class="panel-heading" and contains(.,"'.$percent.'")]',
        ];
        foreach ($xpaths as $xpath) {
            $msg = 'Did not find xpath '.$xpath;
            $this->assertGreaterThan(0, $crawler->filterXPath($xpath)->count().$msg);
        }
    }

    /**
     * @param $playername
     * @dataProvider playerProvider
     */
    public function testAddOwnedCardAction($playername)
    {
        $client = $this->loginAs();
        $repo   = $this->container->get('doctrine')->getRepository('LokiTuoResultBundle:Player');
        /** @var Player $player */
        $player  = $repo->findOneBy(['name' => $playername]);
        $crawler = $client->request('GET', '/player/'.$player->getId().'/cards');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $deck   = $player->getDeck();
        $xpaths = [];
        /** @var OwnedCard $ownedCard */
        foreach ($deck as $ownedCard) {
            $xpaths[$ownedCard->getCard()->getName()] = [
                'path'   => './/td[normalize-space()="'.$ownedCard->getCard()->getName().'"]',
                'amount' => 2,
            ];
        }

        foreach ($xpaths as $cardName => $xpath) {
            $this->assertEquals($xpath['amount'], $crawler->filterXPath($xpath['path'])->count(), 'With Card: '.$cardName);
        }

        // Add Card
        $cardToAdd = 'Rumbler Rickshaw';
        $amount    = 1;
        $level     = null;
        $body      = [
            'owned_card_card'   => $cardToAdd,
            'owned_card_amount' => $amount,
            'owned_card_level'  => $level,
        ];
        $url = '/player/'.$player->getId().'/card';
        $client->request('POST', $url, $body);

        $response = $client->getResponse();
        $this->assertSame(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals($cardToAdd, $responseData['name']);
        $this->assertEquals($level, $responseData['level']);
        $this->assertEquals($amount, $responseData['amount']);

        //Check if Card is shown
        $crawler             = $client->request('GET', '/player/'.$player->getId().'/cards');
        $xpaths[$cardToAdd]  = [
            'path'   => './/td[normalize-space()="'.$cardToAdd.'"]',
            'amount' => 1,
        ];

        foreach ($xpaths as $cardName => $xpath) {
            $this->assertEquals($xpath['amount'], $crawler->filterXPath($xpath['path'])->count(), 'With Card: '.$cardName);
        }

        //Remove Card
        $url = '/player/'.$player->getId().'/card/reduce';
        $client->request('DELETE', $url, $body);

        $response = $client->getResponse();
//        var_dump($response->getContent());die();
        $this->assertSame(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals($cardToAdd, $responseData['name']);
        $this->assertEquals($level, $responseData['level']);
        $this->assertEquals(0, $responseData['amount']);

        $crawler             = $client->request('GET', '/player/'.$player->getId().'/cards');
        $xpaths[$cardToAdd]  = [
            'path'   => './/td[normalize-space()="'.$cardToAdd.'"]',
            'amount' => 0,
        ];

        foreach ($xpaths as $cardName => $xpath) {
            $this->assertEquals($xpath['amount'], $crawler->filterXPath($xpath['path'])->count(), 'With Card: '.$cardName);
        }
    }
}
