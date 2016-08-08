<?php

namespace LokiTuoResultBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'Loki',
            'PHP_AUTH_PW'   => 'wus3ldus3l',
        ));

        $crawler = $client->request('GET', '/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
