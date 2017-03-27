<?php

namespace LokiTuoResultBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class LokiTuoBgeImportCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('loki:tuo:bge:import')
            ->setAliases(['l:t:b:i'])
            ->setDescription('Reads the bges.txt from the tuo and imports all BattleGround Effects')
            ->addArgument('filepath', InputArgument::REQUIRED, 'The path to the bges.txt file.')
            ->addOption('pretend', 'p', InputOption::VALUE_NONE, 'Pretend will not save the data into the Database');
        $this->setHelp(<<<'EOT'
The <info>%command.name%</info> command reads a Textfile which is included in the tyrant-unleashed-optimizer
The Textfile is usually called <info>bges.txt</info> and contains information about BattleGroundEffects (BGEs in short).
The extracted information is then Saved into the Database.

The filestructure is - as of now - very strict. Every BGE needs to be  in this form
 <comment>NAME:DESCRIPTION</comment>

Alternatively, you can just read and Transform them, to check if the File has the Correct structure.
If this mode is enabled, no Data will be saved to the DB:

<info>%command.name% --pretend</info>

EOT
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filepath = $input->getArgument('filepath');
        if ($input->getOption('pretend')) {
            $persister = $this->getContainer()->get('loki_tuo_result.persister.null');
        } else {
            $persister = $this->getContainer()->get('loki_tuo_result.persister.database');
        }
        $logger = new ConsoleLogger($output);
        $persister->setLogger($logger);
        $reader = $this->getContainer()->get('loki_tuo_result.battlegroundeffect.reader');
        $reader->setLogger($logger);
        $reader->setPersister($persister);
        $count = $reader->readFile($filepath);

        $output->writeln('There were <info>'.$count.'</info> Entries to be persisted.');
    }
}
