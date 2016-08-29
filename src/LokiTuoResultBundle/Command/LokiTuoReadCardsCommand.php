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
        $logger = new ConsoleLogger($output);
        $path = $input->getArgument('dataPath');
        $logger->debug(" Filepath Read: ".$path);
//        $path = $this->getContainer()->get('kernel')->getRootDir() . "/../data/";
        if ($input->getOption('option')) {
            // ...
        }
        $reader = $this->getContainer()->get('loki_tuo_result.card.reader');
        $reader->setLogger($logger);
        $filenames = [];
        for ($i = 1; $i <= 10; $i++) {
            $filenames[$i] = realpath($path."/cards_section_".$i.".xml");
            $logger->debug("Adding File to read: ".$filenames[$i]);
        }
        $count = $reader->saveCardFiles($filenames);

        $output->writeln("Persisted $count card Files.");
        return 0;
    }
}
