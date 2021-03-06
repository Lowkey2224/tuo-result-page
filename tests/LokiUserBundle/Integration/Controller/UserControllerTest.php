<?php

namespace LokiUserBundle\Tests\Integration\Controller;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class UserControllerTest
 * @package LokiUserBundle\Tests\Integration\Controller
 * @runTestsInSeparateProcesses
 */
class UserControllerTest extends WebTestCase
{
    const USER             = 'foo';
    const PASSWORD_CORRECT = 'foo';

    public function testIndex()
    {
        $client = $this->loginAs(self::USER, self::PASSWORD_CORRECT);
        $this->assertContains('Welcome, '.self::USER, $client->getResponse()->getContent());

        $link = $client->getCrawler()
            ->filter('a:contains("Users")')
            ->eq(0)
            ->link();
        $client->click($link);

        $this->assertContains('All Users', $client->getResponse()->getContent());
    }

    public function testLoginFail()
    {
        $client = $this->loginAs('sadasda', 'sadaadsdasads');
        $this->assertContains('Invalid credentials.', $client->getResponse()->getContent());
    }

    public function testPromoteAndDemote()
    {
        $client = $this->loginAs(self::USER, self::PASSWORD_CORRECT);
        $this->assertContains('Welcome, '.self::USER, $client->getResponse()->getContent());
        $crawler = $client->request('GET', '/user/');

        $user    = 'user';
        $promote = 'Promote';
        $demote  = 'Demote';

        $this->assertHasColumn($crawler, $user, 'ROLE_USER');
        $crawler = $this->clicPromoteOrDemotekLink($crawler, $client, $user, $promote);
        $this->assertHasColumn($crawler, $user, 'ROLE_MODERATOR, ROLE_USER');
        $crawler = $this->clicPromoteOrDemotekLink($crawler, $client, $user, $demote);
        $this->assertHasColumn($crawler, $user, 'ROLE_USER');
    }

    public function testDeactivateActivate()
    {
        $client = $this->loginAs(self::USER, self::PASSWORD_CORRECT);
        $this->assertContains('Welcome, '.self::USER, $client->getResponse()->getContent());
        $crawler = $client->request('GET', '/user/');

        $user       = 'user';
        $deactivate = 'deactivate';
        $activate   = 'activate';
        $this->assertHasColumn($crawler, $user, 'active');
        $crawler = $this->clicPromoteOrDemotekLink($crawler, $client, $user, $deactivate);
        $this->assertHasColumn($crawler, $user, 'inactive');
        $crawler = $this->clicPromoteOrDemotekLink($crawler, $client, $user, $activate);
        $this->assertHasColumn($crawler, $user, 'active');
    }

    /**
     * @param string $user
     * @param string $password
     *
     * @return \Symfony\Bundle\FrameworkBundle\Client
     */
    protected function loginAs($user = self::USER, $password = self::PASSWORD_CORRECT)
    {
        $client  = static::createClient();
        $crawler = $client->request('GET', '/login');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('Log in', $client->getResponse()->getContent());
        $form              = $crawler->selectButton('_submit')->form();
        $form['_username'] = $user;
        $form['_password'] = $password;
        $client->followRedirects();
        $client->submit($form);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $client->followRedirects(false);

        return $client;
    }

    private function assertHasColumn(Crawler $crawler, $user, $roles)
    {
        $rowXpath   = './/tbody/tr[td[normalize-space()="%s"]]';
        $rolesXpath = $rowXpath.'/td[normalize-space()="%s"]';
        $xpath      = sprintf($rolesXpath, $user, $roles);
        $text       = $crawler->filterXPath($xpath)->text();
        $this->assertEquals($roles, trim($text));
    }

    private function clicPromoteOrDemotekLink(Crawler $crawler, Client $client, $user, $linkName)
    {
        $rowXpath  = './/tbody/tr[td[normalize-space()="%s"]]';
        $linkXpath = $rowXpath.'/td[contains(normalize-space(),"%s")]/a[normalize-space()="%s"]';
        $xpath     = sprintf($linkXpath, $user, $linkName, $linkName);
        $link      = $crawler->filterXPath($xpath)->link();
        $client->click($link);

        return $client->followRedirect();
    }
}
