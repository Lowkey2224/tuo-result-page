<?php


namespace Tests\LokiTuoResultBundle\Integration\Command;

use Doctrine\Common\Persistence\ObjectManager;
use LokiTuoResultBundle\Command\LokiTuoImportFileCommand;
use LokiTuoResultBundle\Entity\Mission;
use LokiTuoResultBundle\Entity\ResultFile;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class TuoResultV2ReadTest extends KernelTestCase
{
    /** @var Application $application */
    private $application;
    /** @var ObjectManager $em */
    private $em;

    public function testReadImportV2()
    {
        self::bootKernel();
        $this->application = new Application(self::$kernel);
        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->createResultFile();
        $this->executeImport();
    }

    private function createResultFile($file = '/result_v2.json')
    {
        $filePath = Util::filePath() . $file;
        $content = file_get_contents($filePath);
        $json = json_decode($content);
        $ctn = $this->em->getRepository('LokiTuoResultBundle:Guild')->findOneBy(['name' => 'CTN']);
        $ctp = $this->em->getRepository('LokiTuoResultBundle:Guild')->findOneBy(['name' => 'CTp']);
        $loki = $this->em->getRepository('LokiTuoResultBundle:Player')->findOneBy(['name' => 'loki']);
        $loken = $this->em->getRepository('LokiTuoResultBundle:Player')->findOneBy(['name' => 'loken']);
        foreach ($json->missions as $mission) {
            foreach ($mission->results as $result) {
                if ($result->player == "loki") {
                    $result->player_id = $loki->getId();
                    $result->guild_id = $ctn->getId();
                } elseif ($result->player == "loken") {
                    $result->player_id = $loken->getId();
                    $result->guild_id = $ctp->getId();
                }
            }
        }
        $resultFile = new ResultFile();
        $resultFile->setVersion(2);
        $resultFile->setOriginalName("results.json");
        $resultFile->setContent(json_encode($json));
        $resultFile->setComment("By PHPUnit");
        $this->em->persist($resultFile);
        $this->em->flush();
    }

    private function executeImport()
    {
        $this->application->add(new LokiTuoImportFileCommand());
        /** @var ContainerAwareCommand $command */
        $command = $this->application->find('loki:tuo:result:import');
        $command->setContainer(self::$kernel->getContainer());
        $commandTester = new CommandTester($command);
        $missionName = 'Test Mission Version2-23';
        $missionRepo = $this->em->getRepository('LokiTuoResultBundle:Mission');
        $resultRepo = $this->em->getRepository('LokiTuoResultBundle:Result');
        $mission = $missionRepo->findOneBy(['name' => $missionName]);
        $this->assertNull($mission, 'Already found a mission with name ' . $missionName);

        $commandTester->execute([
            'command' => $command->getName(),
        ]);

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $expected = 'Persisted a total of 4 Results.';
        $this->assertContains($expected, $output);
        // Test they are really in the DB

        $mission = $missionRepo->findOneBy(['name' => $missionName]);
        $this->assertInstanceOf(Mission::class, $mission);

        $results = $resultRepo->findBy(['mission' => $mission]);
        $this->assertCount(2, $results);
        $res = $results[0];
        $res2 = $results[1];
        $this->assertEquals(408, $res->getPercent());
        $this->assertEquals(303, $res2->getPercent());
    }
}