<?php

namespace App\LokiTuoResultBundle\Command;

use App\LokiTuoResultBundle\Service\Reader\Service;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class LokiTuoResReadFileCommand extends Command
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
            ->setName('loki:tuo:result:read')
            ->setDescription('this Method will read a Tuo Result File, and save it to the Database')
            ->addArgument('filename', InputArgument::OPTIONAL, 'Argument description', 'result.txt')
            ->setHelp('This command allows you to create users...');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filePath = $input->getArgument('filename');
        $logger   = new ConsoleLogger($output);
        $this->reader->setLogger($logger);
        $id = $this->reader->readFile($filePath);

        $output->writeln("Read File with id $id.");

        return 0;
    }
}
