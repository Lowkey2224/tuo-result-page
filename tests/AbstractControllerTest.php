<?php

/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 28.11.16
 * Time: 16:38
 */

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


abstract class AbstractControllerTest extends WebTestCase
{

    const USER = "foo";
    const PASSWORD_CORRECT = "foo";


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
        /*$crawler =*/ $client->submit($form);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $client->followRedirects(false);



        return $client;
    }

}