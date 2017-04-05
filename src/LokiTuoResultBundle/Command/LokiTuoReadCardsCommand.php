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
            ->setName('loki:tuo:cards:read')
            ->setDescription('Reads all Files in the given Directory, and saves the data-files into the Database')
            ->addArgument('dataPath', InputArgument::REQUIRED, 'Argument description ending with /')
            ->addOption('option', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = new ConsoleLogger($output);
        $path   = realpath($input->getArgument('dataPath'));
        $logger->debug(' Filepath Read: ' . $path);
        $reader = $this->getContainer()->get('loki_tuo_result.card.reader');
        $reader->setLogger($logger);
        $files     = scandir($path);
        $pattern   = '/^cards_section_\d\d?.xml/m';
        $cardFiles = array_filter($files, function ($item) use ($pattern) {
            return preg_match($pattern, $item) === 1;
        });
        $cardFiles = array_map(function ($item) use ($path) {
            return $path . '/' . $item;
        }, $cardFiles);
        $count = $reader->saveCardFiles($cardFiles);

        $output->writeln("Persisted $count card Files.");

        return 0;
    }
}
