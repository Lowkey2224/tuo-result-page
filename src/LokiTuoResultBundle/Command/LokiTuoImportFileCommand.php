<?php

namespace App\LokiTuoResultBundle\Command;

use App\LokiTuoResultBundle\Service\Reader\Service;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class LokiTuoImportFileCommand extends Command
{
    /** @var Service */
    private $reader;

    public function __construct(Service $reader)
    {
        $this->reader = $reader;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('loki:tuo:result:import')
            ->setDescription('Imports the Result with the given Id')
            ->addArgument('fileId', InputArgument::OPTIONAL, 'Argument description', 'next');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fileId = $input->getArgument('fileId');
        $logger = new ConsoleLogger($output);
        $this->reader->setLogger($logger);
        $count = $this->reader->importFileById($fileId);

        $output->writeln('Persisted a total of ' . $count . ' Results.');

        return 0;
    }
}
