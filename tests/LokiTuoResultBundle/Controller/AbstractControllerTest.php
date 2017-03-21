<?php

/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 28.11.16
 * Time: 16:38
 */

namespace LokiTuoResultBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;


abstract class AbstractControllerTest extends WebTestCase
{

    /** @var  ContainerInterface */
    protected $container;

    public function setUp()
    {
        self::bootKernel();

        $this->container = self::$kernel->getContainer();
    }

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
        $form = $crawler->selectButton('_submit')->form();
        $form['_username'] = $user;
        $form['_password'] = $password;
        $client->followRedirects();
        $client->submit($form);
        $client->followRedirects(false);


        return $client;
    }

    public static function adminUserProvider()
    {
        return [
            'foo' => ['foo', 'foo'],
            'bar' => ['bar', 'bar'],
            'baz' => ['baz', 'baz'],
        ];

    }


    public static function playerProvider()
    {
        return [
            'loki' => ['loki']
        ];
    }

    public static function playerResultProvider()
    {
        return [
            'loki' => ['loki', 'iron mutant-5', '80.8']
        ];
    }

    /**
     * Clicks the Link with the given xpath
     * @param Client $client
     * @param $xpath
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    public function clickLinkXpath(Client $client, $xpath)
    {
        $link = $client->getCrawler()->filterXPath($xpath)->link();
        return $client->click($link);
    }

    /**
     * Clicks the Link with the given xpath
     * @param Client $client
     * @param string $name
     * @param int $number the number of the link to click
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    public function clickLinkName(Client $client, $name, $number = 0)
    {
        $path = sprintf('a:contains("%s")', $name);
        $link = $client->getCrawler()->filter($path)->eq($number)->link();
        return $client->click($link);
    }

}