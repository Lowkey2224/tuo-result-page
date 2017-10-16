<?php

namespace LokiTuoResultBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class LokiTuoImportCardsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('loki:tuo:cards:import')
            ->setDescription('Imports Card Files from the Database, and creates Card Models from it')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'If set, all Files will be reimported');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $reader = $this->getContainer()->get('loki_tuo_result.card.persister');
        $force = $input->getOption('force');
        $reader->setLogger(new ConsoleLogger($output));
        $count = $reader->importCards($force);
        $output->writeln("Imported $count cards.");

        return 0;
    }
}