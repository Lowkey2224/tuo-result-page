<?php

namespace LokiTuoResultBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class LokiTuoImportFileCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('loki:tuo:result:import')
            ->setDescription('Imports the Result with the given Id')
            ->addArgument('fileId', InputArgument::OPTIONAL, 'Argument description', "next")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fileId = $input->getArgument('fileId');
        $reader = $this->getContainer()->get('loki_tuo_result.reader');
        $logger = new ConsoleLogger($output);
        $reader->setLogger($logger);
        $count = $reader->importFileById($fileId);

        $output->writeln('Persisted a total of '.$count. " Results.");
        return 0;
    }
}
