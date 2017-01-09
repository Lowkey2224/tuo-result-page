<?php

namespace LokiTuoResultBundle\Tests\Controller;

class CardControllerTest extends \AbstractControllerTest
{
    public function testIndex()
    {
        $client = $this->loginAs();

        $crawler = $client->request('GET', '/');
    }
}
