<?php

namespace LokiUserBundle\Tests\Controller;

class DefaultControllerTest extends \AbstractControllerTest
{


    public function testIndex()
    {
        $client = $this->loginAs(self::USER, self::PASSWORD_CORRECT);
        $this->assertContains('Welcome, '.self::USER, $client->getResponse()->getContent());

//        $crawler = $client->request('GET', '/user/');
        $client->request( 'GET', '/user/');

        $this->assertContains('All Users', $client->getResponse()->getContent());
    }

    public function testLoginFail()
    {
        $client = $this->loginAs("sadasda", "sadaadsdasads");
        $this->assertContains('Invalid credentials.', $client->getResponse()->getContent());
    }
}
