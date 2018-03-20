<?php

namespace Tests\LokiTuoResultBundle\Integration\Command;

use LokiTuoResultBundle\Command\LokiTuoBgeImportCommand;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class BgeImportTest extends KernelTestCase
{
    public function testExecute()
    {
        $kernel = static::createKernel();
        $kernel->boot();

        $application = new Application($kernel);

        $em = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        $filePath = Util::filePath() . '/bges.txt';
        /** @var Command $command */
        $command = $application->find('loki:tuo:bge:import');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'filepath' => $filePath,
        ]);

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $expected = 'There were 7 Entries to be persisted.';
        $this->assertContains($expected, $output);
        // Test they are really in the DB
        $bges = $em->getRepository('LokiTuoResultBundle:BattleGroundEffect')->findAll();
        $this->assertCount(9, $bges);
    }
}
