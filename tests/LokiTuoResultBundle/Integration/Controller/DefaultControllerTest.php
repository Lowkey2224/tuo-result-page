<?php

namespace Tests\LokiTuoResultBundle\Integration\Controller;

/**
 * Class DefaultControllerTest
 * @package Tests\LokiTuoResultBundle\Integration\Controller
 * @runTestsInSeparateProcesses
 */
class DefaultControllerTest extends AbstractControllerTest
{
    public function testIndex()
    {
        $client = $this->loginAs();
        $client->request('GET', '/');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('Create Simulation Script', $client->getResponse()->getContent());
    }
}
