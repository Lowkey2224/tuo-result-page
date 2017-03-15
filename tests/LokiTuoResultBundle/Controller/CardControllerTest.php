<?php

namespace LokiTuoResultBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;

class CardControllerTest extends AbstractControllerTest
{
    private $cards = ["Malika","Stonewall Garrison","Inheritor of Hope","Menacing Interrogator","Rumbler Rickshaw","Sinuous Dam","Xebor Comet","Xeno Reanimator"];

    public function testIndex()
    {
        $client = $this->loginAs();
        $client->request('GET', '/card');
        $client->followRedirect();
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

    public function testAll()
    {
        $client = $this->loginAs();
        $client->request('GET', '/card/all');
        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertArraySubset($this->cards, json_decode($response->getContent(), true));
    }
}
