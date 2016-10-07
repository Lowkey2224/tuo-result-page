<?php

namespace LokiTuoResultBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class LokiTuoDeckImportCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('loki:tuo:deck:import')
            ->setDescription('Imports the Owned Cards and Cards in Deck, into the Database from a Mass_Sim Script.')
            ->addArgument('simScript', InputArgument::OPTIONAL, 'Argument description', "mass_sim.sh")
            ->addOption('option', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $argument = $input->getArgument('simScript');

//        if ($input->getOption('option')) {
//             ...
//        }~
        $logger = new ConsoleLogger($output);
        $massSimReader = $this->getContainer()->get('loki_tuo_result.owned_card.mass_sim_reader');
        $massSimReader->setLogger($logger);
        $res  = $massSimReader->getPlayerCardMap($argument);

        $res = $massSimReader->savePlayerCardMap($res);
        foreach ($res as $playerName => $cardArray) {
        //            $logger->debug("$playerName ".implode(", ", $cardArray));
        }


        $output->writeln('Command result.');
        return 0;
    }
}
