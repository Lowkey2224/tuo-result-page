<?php

namespace App\LokiTuoResultBundle\Command;

use App\LokiTuoResultBundle\Service\BattleGroundEffectReader\Service;
use App\LokiTuoResultBundle\Service\Persister\DatabasePersister;
use App\LokiTuoResultBundle\Service\Persister\NullPersister;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class LokiTuoBgeImportCommand extends Command
{
    /** @var NullPersister */
    private $nullPersister;
    /** @var DatabasePersister */
    private $databasePersister;
    /** @var Service */
    private $reader;

    public function __construct(NullPersister $nullPersister, DatabasePersister $databasePersister, Service $reader)
    {
        $this->nullPersister = $nullPersister;
        $this->databasePersister = $databasePersister;
        $this->reader = $reader;
        parent::__construct();
    }

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
        $logger = new ConsoleLogger($output);
        $filepath = $input->getArgument('filepath');
        $this->reader->setLogger($logger);
        if ($input->getOption('pretend')) {
            $this->nullPersister->setLogger($logger);
            $this->reader->setPersister($this->nullPersister);
        } else {
            $this->databasePersister->setLogger($logger);
            $this->reader->setPersister($this->databasePersister);
        }

        $count = $this->reader->readFile($filepath);

        $output->writeln('There were <info>' . $count . '</info> Entries to be persisted.');
    }
}
