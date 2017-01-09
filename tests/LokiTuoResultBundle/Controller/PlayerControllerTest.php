<?php

namespace LokiTuoResultBundle\Tests\Controller;

class PlayerControllerTest extends \AbstractControllerTest
{
    public function testShowcardsforplayer()
    {
        $client = $this->loginAs();

        $crawler = $client->request('GET', '/1/cards');
        $this->assertContains('Malika', $client->getResponse()->getContent());
    }
}
