<?php

namespace LokiTuoResultBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;

class CardControllerTest extends AbstractControllerTest
{
    public function testIndex()
    {
        $client = $this->loginAs();
        $client->request('GET', '/card');
        $client->followRedirect();
//        var_dump($client->getResponse()->getContent());
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

    public function testAll()
    {
        $client = $this->loginAs();
        $client->request('GET', '/card/all');
        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $expectedJsonFile =__DIR__."/../files/allCards.json";
        $this->assertJsonStringEqualsJsonFile($expectedJsonFile, $response->getContent());
    }
}
