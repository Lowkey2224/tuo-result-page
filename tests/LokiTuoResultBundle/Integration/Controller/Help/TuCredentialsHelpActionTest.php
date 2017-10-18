<?php

namespace LokiTuoResultBundle\Integration\Controller\Help;

use Tests\LokiTuoResultBundle\Integration\Controller\AbstractControllerTest;

class TuCredentialsHelpActionTest extends AbstractControllerTest
{
    public function testLoggedIn()
    {
        $client = $this->loginAs();
        $client->request('GET', '/help/tuCredentials');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('Step 1: Claim Player', $client->getResponse()->getContent());
        $this->assertContains('Step 2: Get Credentials', $client->getResponse()->getContent());
    }

    public function testNotLoggedIn()
    {
        $client = static::createClient();
        $client->request('GET', '/help/tuCredentials');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('Step 1: Claim Player', $client->getResponse()->getContent());
        $this->assertContains('Step 2: Get Credentials', $client->getResponse()->getContent());
    }
}
