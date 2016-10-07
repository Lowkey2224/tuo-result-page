<?php

namespace LokiTuoResultBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class LokiTuoResReadFileCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('loki:tuo:result:read')
            ->setDescription('this Method will read a Tuo Result File, and save it to the Database')
            ->addArgument('filename', InputArgument::OPTIONAL, 'Argument description', "result.txt")
            ->addArgument('guild', InputArgument::OPTIONAL, 'Argument description', "CTP")
            ->setHelp("This command allows you to create users...")

        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filePath = $input->getArgument('filename');
        $reader = $this->getContainer()->get('loki_tuo_result.reader');
        $logger = new ConsoleLogger($output);
        $reader->setLogger($logger);
        $id = $reader->readFile($filePath, $input->getArgument('guild'));

        $output->writeln("Read File with id $id");
        return 0;
    }
}
