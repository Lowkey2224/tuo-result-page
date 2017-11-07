<?php

namespace Tests\LokiTuoResultBundle\Integration\Controller;

use Symfony\Component\HttpFoundation\Response;

/**
 * Class CardControllerTest
 * @package Tests\LokiTuoResultBundle\Integration\Controller
 * @runTestsInSeparateProcesses
 */
class CardControllerTest extends AbstractControllerTest
{
    private $cards = ['Malika', 'Stonewall Garrison', 'Inheritor of Hope', 'Menacing Interrogator', 'Rumbler Rickshaw', 'Sinuous Dam', 'Xebor Comet', 'Xeno Reanimator'];

    public function testIndex()
    {
        $client = $this->loginAs();
        $client->request('GET', '/card/');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

    public function testAll()
    {
        $client = $this->loginAs();
        $client->request('GET', '/card/all');
        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $res = json_decode($response->getContent(), true);
        foreach ($this->cards as $card) {
            $this->assertContains($card, $res);
        }
    }
}
