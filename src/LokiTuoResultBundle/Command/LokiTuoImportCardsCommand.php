<?php

namespace LokiTuoResultBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class LokiTuoImportCardsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('loki:tuo:cards:import')
            ->setDescription('Imports Card Files from the Database, and creates Card Models from it')
            ->addArgument('argument', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
//        $argument = $input->getArgument('argument');

        if ($input->getOption('option')) {
            // ...
        }
        $reader = $this->getContainer()->get('loki_tuo_result.card.persister');
        $reader->setLogger(new ConsoleLogger($output));
        $count = $reader->importCards();
        $output->writeln("Imported $count cards.");
        return 0;
    }
}
