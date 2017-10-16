<?php

namespace LokiTuoResultBundle\Integration\Controller\Player;

use LokiTuoResultBundle\Entity\Player;
use LokiTuoResultBundle\Integration\Tests\Controller\AbstractControllerTest;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\DomCrawler\Crawler;

class EditPlayerTest extends AbstractControllerTest
{
    /**
     * covers PlayerController::listAllPlayersAction().
     *
     * @dataProvider playerProvider
     */
    public function testEditPlayersAction()
    {
        $client = $this->loginAs();

        $client->request('GET', '/player/');
        $crawler = $this->clickLinkName($client, 'Edit');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('button:contains("Delete")')->count());
        $this->assertEquals(1, $crawler->filterXPath('//button[@name= "player[submit]"]')->count());
    }

    /**
     * covers PlayerController::listAllPlayersAction().
     *
     * @dataProvider playerProvider
     *
     * @param string $playerName
     */
    public function testEditPlayerChangeGuildAction($playerName)
    {
        $client    = $this->loginAs();
        $repo      = $this->container->get('doctrine')->getRepository('LokiTuoResultBundle:Player');
        $guildRepo = $this->container->get('doctrine')->getRepository('LokiTuoResultBundle:Guild');
        $player    = $repo->findOneBy(['name' => $playerName]);
        $oldGuild  = $player->getGuild();
        $newGuild  = $guildRepo->findOneBy(['name' => 'CTP']);
        //Go to Edit Page
        $crawler = $this->getEditRoute($client, $player);
        //Fill in the Form
        $form                  = $this->getPlayerForm($crawler);
        $form['player[guild]'] = $newGuild->getId();
        $client->submit($form);
        //Check the Change
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $client->followRedirect();
        $this->assertPlayerHasColumn($client->getCrawler(), $playerName, 'CTP');

        //Change it back
        $crawler               = $this->getEditRoute($client, $player);
        $form                  = $this->getPlayerForm($crawler);
        $form['player[guild]'] = $oldGuild->getId();
        $client->submit($form);
        //Check the Change
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $client->followRedirect();
        $this->assertPlayerHasColumn($client->getCrawler(), $playerName, 'CTN');
    }

    /**
     * @param $playerName
     * @dataProvider playerProvider
     */
    public function testClaimPlayer($playerName)
    {
        $client = $this->loginAs();
        $repo   = $this->container->get('doctrine')->getRepository('LokiTuoResultBundle:Player');
        $user   = $this->getUser();
        $player = $repo->findOneBy(['name' => $playerName]);
        //Go to Edit Page
        $crawler = $this->getEditRoute($client, $player);
        //Fill in the Form & confirm claim
        $form                  = $this->getPlayerForm($crawler);
        $form['player[owner]'] = $user->getId();
        $client->submit($form);
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $client->followRedirect();
        $this->clickLinkName($client, 'confirm');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $client->followRedirect();
        //Check
        $this->assertPlayerHasColumn($client->getCrawler(), $playerName, $user->getUsername());

        //Change it back
        $crawler               = $this->getEditRoute($client, $player);
        $form                  = $this->getPlayerForm($crawler);
        $form['player[owner]'] = null;
        $client->submit($form);
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $client->followRedirect();
        //Check the Change
        $this->assertPlayerHasColumn($client->getCrawler(), $playerName, $user->getUsername(), 0);
    }

    private function assertPlayerHasColumn(Crawler $crawler, $playerName, $column, $count = 1)
    {
        $this->assertTableHasCell($crawler, $playerName, $column, $count);
    }

    /**
     * @param Crawler $crawler
     *
     * @return \Symfony\Component\DomCrawler\Form
     */
    private function getPlayerForm(Crawler $crawler)
    {
        return $this->getFormById($crawler, 'player_submit', 'button');
    }

    private function getEditRoute(Client $client, Player $player)
    {
        return $client->request('GET', '/player/'.$player->getId().'/edit');
    }
}
