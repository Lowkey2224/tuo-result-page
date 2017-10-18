<?php

namespace LokiTuoResultBundle\Integration\Controller\Guild;

use Tests\LokiTuoResultBundle\Integration\Controller\AbstractControllerTest;

class IndexTest extends AbstractControllerTest
{
    /**
     * covers GuildController::indexAxtion().
     */
    public function testGuildIndex()
    {
        $client = $this->loginAs();

        $crawler = $this->clickLinkName($client, 'Guilds');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filterXPath('.//tr/td[normalize-space()="CTP"]')->count());
        $this->assertEquals(1, $crawler->filterXPath('.//tr/td[normalize-space()="CTN"]')->count());
    }
}
