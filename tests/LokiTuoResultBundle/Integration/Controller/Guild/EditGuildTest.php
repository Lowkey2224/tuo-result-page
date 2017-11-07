<?php

namespace LokiTuoResultBundle\Integration\Controller\Guild;

use Symfony\Bundle\FrameworkBundle\Client;
use Tests\LokiTuoResultBundle\Integration\Controller\AbstractControllerTest;

/**
 * Class EditGuildTest
 * @package LokiTuoResultBundle\Integration\Controller\Guild
 * @runTestsInSeparateProcesses
 */
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
        $client->request('GET', '/guild/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->clickLinkName($client, 'Add guild');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $form                   = $this->getFormById($client->getCrawler(), 'guild_submit');
        $form['guild[name]']    = 'TestGuild';
        $form['guild[enabled]']->tick();
        $client->submit($form);
    }

    private function createDouble(Client $client)
    {
        $this->create($client);
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $client->followRedirect();
        $this->assertTableHasCell($client->getCrawler(), 'TestGuild', 'active');
        $this->create($client);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $xpath = sprintf('li:contains("%s")', 'This Guild already Exists!');
        $this->assertEquals(1, $client->getCrawler()->filter($xpath)->count());
    }

    private function edit(Client $client)
    {
        $client->request('GET', '/guild/');
        //FIXME Why redirect here?
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->clickLinkInTable($client, 'TestGuild', 'Edit');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $form                   = $this->getFormById($client->getCrawler(), 'guild_submit');
        $form['guild[name]']    = 'TestGuildInactive';
        $form['guild[enabled]']->untick();
        $client->submit($form);
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $client->followRedirect();
        $this->assertTableHasCell($client->getCrawler(), 'TestGuildInactive', 'inactive');
    }
}
