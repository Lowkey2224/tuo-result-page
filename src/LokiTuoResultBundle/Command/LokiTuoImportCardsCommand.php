<?php

namespace LokiTuoResultBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class LokiTuoImportCardsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('loki:tuo:cards:import')
            ->setDescription('Imports Card Files from the Database, and creates Card Models from it');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $reader = $this->getContainer()->get('loki_tuo_result.card.persister');
        $reader->setLogger(new ConsoleLogger($output));
        $count = $reader->importCards();
        $output->writeln("Imported $count cards.");

        return 0;
    }
}
