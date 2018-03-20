<?php

namespace App\LokiTuoResultBundle\Command;

use App\LokiTuoResultBundle\Service\CardReader\Persister;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class LokiTuoImportCardsCommand extends Command
{
    /** @var Persister */
    private $persister;

    public function __construct(Persister $persister)
    {
        $this->persister = $persister;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('loki:tuo:cards:import')
            ->setDescription('Imports Card Files from the Database, and creates Card Models from it')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'If set, all Files will be reimported');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $force = $input->getOption('force');
        $this->persister->setLogger(new ConsoleLogger($output));
        $count = $this->persister->importCards($force);
        $output->writeln("Imported $count cards.");

        return 0;
    }
}