<?php

namespace Tests\LokiTuoResultBundle\Integration\Command;

use LokiTuoResultBundle\Command\LokiTuoBgeImportCommand;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class BgeImportTest extends KernelTestCase
{
    public function testExecute()
    {
        self::bootKernel();
        $application = new Application(self::$kernel);
        $em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        $application->add(new LokiTuoBgeImportCommand());
        $filePath = Util::filePath() . '/bges.txt';
        /** @var ContainerAwareCommand $command */
        $command = $application->find('loki:tuo:bge:import');
        $command->setContainer(self::$kernel->getContainer());
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
