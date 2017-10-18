<?php

/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 28.11.16
 * Time: 16:38.
 */

namespace Tests\LokiTuoResultBundle\Integration\Controller;

use LokiUserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DomCrawler\Crawler;

abstract class AbstractControllerTest extends WebTestCase
{
    /** @var ContainerInterface */
    protected $container;

    public function setUp()
    {
        self::bootKernel();

        $this->container = self::$kernel->getContainer();
    }

    const USER             = 'foo';
    const PASSWORD_CORRECT = 'foo';

    /**
     * @param string $user
     * @param string $password
     *
     * @return \Symfony\Bundle\FrameworkBundle\Client
     */
    protected function loginAs($user = self::USER, $password = self::PASSWORD_CORRECT)
    {
        $client            = static::createClient();
        $crawler           = $client->request('GET', '/login');
        $form              = $crawler->selectButton('_submit')->form();
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
            'loki' => ['loki'],
        ];
    }

    public static function playerResultProvider()
    {
        return [
            'loki' => ['loki', 'iron mutant-5', '80.8'],
        ];
    }

    /**
     * Clicks the Link with the given xpath.
     *
     * @param Client $client
     * @param $xpath
     *
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    public function clickLinkXpath(Client $client, $xpath)
    {
        $link = $client->getCrawler()->filterXPath($xpath)->link();

        return $client->click($link);
    }

    /**
     * Clicks the Link with the given Link text or Linkname.
     *
     * @param Client $client
     * @param string $name
     * @param int    $number the number of the link to click
     *
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    public function clickLinkName(Client $client, $name, $number = 0)
    {
        $path = sprintf('.//a[@name="%s" or normalize-space()="%s"]', $name, $name);
        $link = $client->getCrawler()->filterXPath($path)->eq($number)->link();

        return $client->click($link);
    }

    /**
     * @param string $username
     *
     * @return User
     */
    protected function getUser($username = self::USER)
    {
        $repo = $this->container->get('doctrine')->getRepository('LokiUserBundle:User');

        return $repo->findOneBy(['username' => $username]);
    }

    /**
     * @param Crawler $crawler
     * @param $id
     * @param string $type
     *
     * @return \Symfony\Component\DomCrawler\Form
     */
    protected function getFormById(Crawler $crawler, $id, $type = '*')
    {
        return $crawler->filterXPath(sprintf('.//%s[@id="%s"]', $type, $id))->form();
    }

    protected function assertTableHasCell(Crawler $crawler, $rowName, $column, $count = 1)
    {
        $path = sprintf('.//tr[td[normalize-space()="%s"]]/td[normalize-space()="%s"]', $rowName, $column);
        $this->assertEquals($count, $crawler->filterXPath($path)->count());
    }

    protected function clickLinkInTable(Client $client, $row, $linkName)
    {
        $row  = $client->getCrawler()->filterXPath(sprintf('.//tr[td[normalize-space()="%s"]]', $row));
        $link = $row->filter(sprintf('a:contains("%s")', $linkName))->link();

        return $client->click($link);
    }

    /**
     * @return string
     */
    protected function getFilePath()
    {
        return self::filePath();
    }

    public static function filePath()
    {
        return __DIR__ . '/../../files/';
    }

    protected function assertFlashMessage(Crawler $crawler, $type, $message)
    {
        $xpath = './/div[contains(@class, "alert alert-%s") and contains(normalize-space(), "%s")]';
        $this->assertEquals(1, $crawler->filterXPath(sprintf($xpath, $type, $message))->count());
    }
}
