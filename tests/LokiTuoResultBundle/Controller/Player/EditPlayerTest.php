<?php


namespace LokiTuoResultBundle\Controller\Player;


use LokiTuoResultBundle\Tests\Controller\AbstractControllerTest;

class EditPlayerTest extends AbstractControllerTest
{
    /**
     * covers PlayerController::listAllPlayersAction()
     * @dataProvider playerProvider
     * @param $player
     */
    public function testListAllPlayersAction($player)
    {
        $client = $this->loginAs();

        $client->request('GET', '/player/');
        $link = $client->getCrawler()
            ->filter('a:contains("Players")')
            ->eq(0)
            ->link();
        $crawler = $client->click($link);

    }
}