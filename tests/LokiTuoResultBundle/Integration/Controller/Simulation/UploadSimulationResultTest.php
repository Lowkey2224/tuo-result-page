<?php

namespace LokiTuoResultBundle\Integration\Controller\Simulation;

use LokiTuoResultBundle\Integration\Tests\Controller\AbstractControllerTest;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadSimulationResultTest extends AbstractControllerTest
{
    private $missionName = 'TestMission-81';
    private $successMsg  = '1 Results have been imported';
    private $percent     = '80.8%';
    private $player      = '[CTN] PHPUnitGuy';
    private $cards       = 'Malika, Stonewall Garrison, Menacing Interrogator, Sinuous Dam, Xeno Reanimator, Sinuous Dam';

    public function testUpload()
    {
        $client                       = $this->loginAs();
        $crawler                      = $this->clickLinkName($client, 'Upload Result');
        $form                         = $this->getFormById($crawler, 'result_file_submit');
        $file                         = new UploadedFile($this->getFilePath().'resultTestCrawler.txt', 'result.txt');
        $form['result_file[file]']    = $file;
        $form['result_file[comment]'] = 'Simulation Created with PHPUnit';
        $client->submit($form);
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $client->followRedirect();
        //Check the Result
        $this->assertFlashMessage($client->getCrawler(), 'success', $this->successMsg);
        $crawler = $this->clickLinkName($client, $this->missionName);
        $this->assertEquals(1, $crawler->filterXPath(sprintf('.//h2[.="Results for %s"]', $this->missionName))->count());
        $this->assertEquals(1, $crawler->filterXPath(sprintf('.//div[@class="panel-heading"][contains(.,"%s for mission")]', $this->percent))->count());
        $this->assertEquals(1, $crawler->filterXPath(sprintf('.//div[@class="panel-heading"]/a[normalize-space()="%s"]', $this->player))->count());

        $path = './/div[@class="panel-body"][contains(.,"%s")]';
        foreach (explode(', ', $this->cards) as $card) {
            $this->assertEquals(1, $crawler->filterXPath(sprintf($path, trim($card)))->count(), "Looking for Card $card but it was not found");
        }
    }
}
