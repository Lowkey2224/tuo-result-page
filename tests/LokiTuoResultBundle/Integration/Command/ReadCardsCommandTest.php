<?php

namespace Tests\LokiTuoResultBundle\Integration\Command;

use Doctrine\Common\Persistence\ObjectManager;
use LokiTuoResultBundle\Command\LokiTuoImportCardsCommand;
use LokiTuoResultBundle\Command\LokiTuoReadCardsCommand;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ReadCardsCommandTest extends KernelTestCase
{
    /** @var Application $application */
    private $application;
    /** @var ObjectManager $em */
    private $em;

    public function testReadImport()
    {
        self::bootKernel();
        $this->application = new Application(self::$kernel);
        $this->em          = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->executeRead();
        $this->executeImport();
    }

    private function executeRead()
    {
        $this->application->add(new LokiTuoReadCardsCommand());
        /** @var ContainerAwareCommand $command */
        $command = $this->application->find('loki:tuo:cards:read');
        $command->setContainer(self::$kernel->getContainer());
        $commandTester = new CommandTester($command);
        $bges          = $this->em->getRepository('LokiTuoResultBundle:CardFile')->findAll();
        $countBefore   = count($bges);
        $commandTester->execute([
            'command'  => $command->getName(),
            'dataPath' => Util::filePath(),
        ]);

        // the output of the command in the console
        $output   = $commandTester->getDisplay();
        $expected = 'Persisted 2 card Files.';
        $this->assertContains($expected, $output);
        // Test they are really in the DB
        $bges = $this->em->getRepository('LokiTuoResultBundle:CardFile')->findAll();
        $this->assertCount($countBefore + 2, $bges);
    }

    private function executeImport()
    {
        $this->application->add(new LokiTuoImportCardsCommand());
        /** @var ContainerAwareCommand $command */
        $command = $this->application->find('loki:tuo:cards:import');
        $command->setContainer(self::$kernel->getContainer());
        $commandTester = new CommandTester($command);
        $res           = $this->em->getRepository('LokiTuoResultBundle:Card')->findAll();

        $countBefore = count($res);
        $commandTester->execute([
            'command' => $command->getName(),
        ]);

        // the output of the command in the console
        $output   = $commandTester->getDisplay();
        $expected = 'Imported 2 cards.';
        $this->assertContains($expected, $output);
        // Test they are really in the DB
        $cards = $this->em->getRepository('LokiTuoResultBundle:Card')->findAll();

        $this->assertCount($countBefore + 2, $cards);
    }
}
