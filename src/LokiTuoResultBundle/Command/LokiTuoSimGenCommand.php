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
        for($i=0;$i<count($all); $i++) {
            $all[$i] = $all[$i]->getId();
        }
        foreach ($all as $id)
        {
            $output->writeln("Reimporting File with ID $id");
            $count = $reader->importFileById($id);
            $output->writeln('Persisted a total of '.$count. " Results");
        }

//razogoth, serapherus, excelsitus, tyr cannon, protomech, miasma, albatross-10, iron mutant level 5, iron mutant-10, steel mutant level-5, steel mutant-10, pantheon powered-10, fiercy fury-10, brimstone beckons-10, paramount patriarch-10, the gateway anomaly-10, shot the sheriff-10, supremacy mutant-10, malaphir malevolence-10, emrys' eminence-10, ultimate uruk-10

        return 0;
    }
}
