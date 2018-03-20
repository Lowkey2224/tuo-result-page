<?php

namespace App\LokiTuoResultBundle\Command;

use App\LokiTuoResultBundle\Service\OwnedCards\MassSimReader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class LokiTuoDeckImportCommand extends Command
{
    /** @var MassSimReader */
    private $massSimReader;

    public function __construct(MassSimReader $massSimReader)
    {
        $this->massSimReader = $massSimReader;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('loki:tuo:deck:import')
            ->setDescription('[DEPRECATED]Imports the Owned Cards and Cards in Deck, into the Database from a Mass_Sim Script.')
            ->addArgument('simScript', InputArgument::OPTIONAL, 'Argument description', 'mass_sim.sh')
            ->addOption('option', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $argument = $input->getArgument('simScript');

        $res = $this->massSimReader->getPlayerCardMap($argument);

        $this->massSimReader->savePlayerCardMap($res);

        $output->writeln('Command result.');

        return 0;
    }
}
