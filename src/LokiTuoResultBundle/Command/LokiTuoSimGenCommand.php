<?php

namespace LokiTuoResultBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class LokiTuoSimGenCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('loki:tuo:lastUpdate')
            ->setDescription('...');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $reader = $this->getContainer()->get('loki_tuo_result.reader');
        $logger = new ConsoleLogger($output);
        $reader->setLogger($logger);
        $repo =$this->getContainer()->get('doctrine')->getRepository('LokiTuoResultBundle:ResultFile');
        $all = $repo->findAll();
        for ($i=0; $i<count($all); $i++) {
            $all[$i] = $all[$i]->getId();
        }
        foreach ($all as $id) {
            $output->writeln("Reimporting File with ID $id");
            $count = $reader->importFileById($id);
            $output->writeln('Persisted a total of '.$count. " Results");
        }

        return 0;
    }
}
