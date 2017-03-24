<?php

namespace LokiTuoResultBundle\Controller\Guild;

use LokiTuoResultBundle\Tests\Controller\AbstractControllerTest;
use Symfony\Bundle\FrameworkBundle\Client;


class EditGuildTest extends AbstractControllerTest
{
    /**
     * Test the Positive Creation the a Guild.
     */
    public function testCreateGuild()
    {
        $client = $this->loginAs();
        $this->createDouble($client);
        $this->edit($client);
    }

    private function create(Client $client)
    {
        $client->request('GET', '/guild');
        //FIXME Why redirect here?
        $this->assertEquals(301, $client->getResponse()->getStatusCode());
        $client->followRedirect();

        $this->clickLinkName($client, 'Add Guild');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $form                   = $this->getFormById($client->getCrawler(), 'guild_submit');
        $form['guild[name]']    = 'TestGuild';
        $form['guild[enabled]'] = 1;
        $client->submit($form);
    }

    private function createDouble(Client $client) {
        $this->create($client);
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $client->followRedirect();
        $this->assertTableHasCell($client->getCrawler(), 'TestGuild', 'active');
        $this->create($client);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $xpath = ".//div[contains(@class,'alert-danger')]";
        $this->assertEquals(1, $client->getCrawler()->filterXPath($xpath)->count());
    }

    private function edit(Client $client)
    {
        $client->request('GET', '/guild');
        //FIXME Why redirect here?
        $this->assertEquals(301, $client->getResponse()->getStatusCode());
        $client->followRedirect();
    }
}
