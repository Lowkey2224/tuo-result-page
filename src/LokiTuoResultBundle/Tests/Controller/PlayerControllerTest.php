<?php

namespace LokiTuoResultBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PlayerControllerTest extends WebTestCase
{
    public function testShowcardsforplayer()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/{playerId}/cards');
    }
}
