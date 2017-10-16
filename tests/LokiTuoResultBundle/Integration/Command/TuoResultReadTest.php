<?php

namespace LokiTuoResultBundle\Integration\Tests\Command;

use Doctrine\Common\Persistence\ObjectManager;
use LokiTuoResultBundle\Command\LokiTuoImportFileCommand;
use LokiTuoResultBundle\Command\LokiTuoResReadFileCommand;
use LokiTuoResultBundle\Entity\Mission;
use LokiTuoResultBundle\Integration\Tests\Controller\AbstractControllerTest;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class TuoResultReadTest extends KernelTestCase
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
        $this->application->add(new LokiTuoResReadFileCommand());
        $filePath = AbstractControllerTest::filePath().'/resultTest.txt';
        /** @var ContainerAwareCommand $command */
        $command = $this->application->find('loki:tuo:result:read');
        $command->setContainer(self::$kernel->getContainer());
        $commandTester = new CommandTester($command);
        $resultFiles   = $this->em->getRepository('LokiTuoResultBundle:ResultFile')->findAll();
        $countBefore   = count($resultFiles);
        $commandTester->execute([
            'command'  => $command->getName(),
            'filename' => $filePath,
        ]);

        // the output of the command in the console
        $output   = $commandTester->getDisplay();
        $expected = 'Read File with id ';
        $this->assertContains($expected, $output);
        // Test they are really in the DB
        $resultFiles = $this->em->getRepository('LokiTuoResultBundle:ResultFile')->findAll();
        $this->assertCount($countBefore + 1, $resultFiles);
    }

    private function executeImport()
    {
        $this->application->add(new LokiTuoImportFileCommand());
        /** @var ContainerAwareCommand $command */
        $command = $this->application->find('loki:tuo:result:import');
        $command->setContainer(self::$kernel->getContainer());
        $commandTester = new CommandTester($command);
        $missionName   = 'TestMission-80';
        $missionRepo   = $this->em->getRepository('LokiTuoResultBundle:Mission');
        $resultRepo    = $this->em->getRepository('LokiTuoResultBundle:Result');
        $mission       = $missionRepo->findOneBy(['name' => $missionName]);
        $this->assertNull($mission, 'Already found a mission with name '.$missionName);

        $commandTester->execute([
            'command' => $command->getName(),
        ]);

        // the output of the command in the console
        $output   = $commandTester->getDisplay();
        $expected = 'Persisted a total of 1 Results.';
        $this->assertContains($expected, $output);
        // Test they are really in the DB

        $mission = $missionRepo->findOneBy(['name' => $missionName]);
        $this->assertInstanceOf(Mission::class, $mission);

        $results = $resultRepo->findBy(['mission' => $mission]);
        $this->assertCount(1, $results);
        $res = $results[0];
        $this->assertEquals(808, $res->getPercent());
        $this->assertEquals(808, $res->getPercent());
    }
}
