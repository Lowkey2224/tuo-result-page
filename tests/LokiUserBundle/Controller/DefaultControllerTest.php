<?php

namespace LokiUserBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    const USER = "foo";
    const PASSWORD_CORRECT = "foo";

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


    /**
     * @param string $user
     * @param string $password
     * @return \Symfony\Bundle\FrameworkBundle\Client
     */
    protected function loginAs($user = self::USER, $password = self::PASSWORD_CORRECT)
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('Log in', $client->getResponse()->getContent());
        $form = $crawler->selectButton('_submit')->form();
        $form['_username'] = $user;
        $form['_password'] = $password;
        $client->followRedirects();
        $client->submit($form);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $client->followRedirects(false);
        return $client;
    }
}
