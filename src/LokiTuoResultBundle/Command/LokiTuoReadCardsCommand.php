<?php

namespace LokiTuoResultBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class LokiTuoReadCardsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('loki:tuo:read:cards')
            ->setDescription('...')
            ->addArgument('dataPath', InputArgument::REQUIRED, 'Argument description ending with /')
            ->addOption('option', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getArgument('filename');

//        $path = $this->getContainer()->get('kernel')->getRootDir() . "/../data/";
        if ($input->getOption('option')) {
            // ...
        }
        $reader = $this->getContainer()->get('loki_tuo_result.card.reader');
        $reader->setLogger(new ConsoleLogger($output));
        $filenames = [];
        for ($i = 1; $i <= 10; $i++) {
            $filenames[$i] = realpath($path."/cards_section_".$i.".xml");
        }
        $reader->saveCardFiles($filenames);

        $output->writeln('Command result.');
    }
}
