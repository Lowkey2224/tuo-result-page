<?php


namespace LokiTuoResultBundle\Controller\Guild;


use LokiTuoResultBundle\Tests\Controller\AbstractControllerTest;

class CreateGuildTest extends AbstractControllerTest
{
    public function testCreateGuild()
    {
        $client = $this->loginAs();

        $client->request("GET", "/guild");
        //FIXME Why redirect here?
        $this->assertEquals(301, $client->getResponse()->getStatusCode());
        $client->followRedirect();
//        var_dump($client->getResponse()->getContent());die();

        $this->clickLinkName($client ,"Add Guild");
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $form = $this->getFormById($client->getCrawler(), 'guild_submit');
        $form['guild[name]'] = "TestGuild";
        $form['guild[enabled]'] = "true";
        $client->submit($form);
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $client->followRedirect();
        $this->assertTableHasCell($client->getCrawler(), "TestGuild", 'active');
    }
}