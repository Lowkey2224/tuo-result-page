<?php

namespace LokiTuoResultBundle\Integration\Controller\Simulation;

use LokiTuoResultBundle\Entity\Player;
use Tests\LokiTuoResultBundle\Integration\Controller\AbstractControllerTest;

/**
 * Class CreateSimulationTest
 * @package LokiTuoResultBundle\Integration\Controller\Simulation
 * @runTestsInSeparateProcesses
 */
class CreateSimulationTest extends AbstractControllerTest
{
    private $mission1 = 'Supremacy Mutant-10';
    private $player   = 'loki';

    /**
     * covers PlayerController::listAllPlayersAction().
     *
     * @test
     */
    public function testCreateSimAction()
    {
        $client = $this->loginAs();
        $repo   = $this->container->get('doctrine')->getRepository('LokiTuoResultBundle:Player');
        /** @var Player $player */
        $player  = $repo->findOneBy(['name' => $this->player]);
        $crawler = $this->clickLinkName($client, 'Create Simulation Script');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(1, $crawler->filter('div:contains("create simulation script")')->count());
        $this->assertEquals(1, $crawler->filterXPath('//button[@id="simulation_save"]')->count());
        $form                                 = $this->getFormById($crawler, 'simulation_save');
        $form['simulation[missions]']         = $this->mission1;
        $form['simulation[backgroundeffect]'] = '';
        $form['simulation[structures]']       = '';
        $form['simulation[enemyStructures]']  = '';
        $form['simulation[threadCount]']      = '';
        $form['simulation[guild]']            = $player->getGuild()->getId();
        $form['simulation[players]']          = $player->getId();
        $client->submit($form);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $content         = $client->getResponse()->getContent();
        $content         = $this->removeTimeStampFromScriptFile(trim($content));
        $expectedContent = trim(file_get_contents($this->getFilePath().'mass_sim_v2.sh'));

        $this->assertEquals($expectedContent, $content);
        unset($expectedContent);
        unset($content);
    }

    private function removeTimeStampFromScriptFile($content, $replacement = '%s')
    {
        $regExp = [
            '/[\d\d\/]{17}/',
            '/Member\d+/',
            '/Deck\d+/',
            '/Inventory\d+/',
            '/Member\d+Guild/',
            '/player_id\\\": \d+,/',
            '/MemberGuildId="\d+"/',
        ];
        $replacement = [
            $replacement,
            'Member',
            'Deck',
            'Inventory',
            'MemberGuild',
            'player_id\": null,',
            'MemberGuildId="null"',
        ];

        return preg_replace($regExp, $replacement, $content);
    }
}
