<?php


namespace LokiTuoResultBundle\Controller\Guild;


use LokiTuoResultBundle\Tests\Controller\AbstractControllerTest;

class IndexTest extends AbstractControllerTest
{

    /**
     * covers PlayerController::listAllPlayersAction()
     */
    public function testListAllPlayersAction()
    {
        $client = $this->loginAs();

        $crawler = $this->clickLinkName($client, "Guilds");
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(1,$crawler->filterXPath('.//tr/td[normalize-space()="CTP"]')->count());
        $this->assertEquals(1,$crawler->filterXPath('.//tr/td[normalize-space()="CTN"]')->count());
    }


}