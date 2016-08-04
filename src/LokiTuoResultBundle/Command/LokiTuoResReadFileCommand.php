<?php

namespace LokiTuoResultBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LokiTuoResReadFileCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('Loki:tuoRes:ReadFile')
            ->setDescription('this Method will read a Tuo Result File, and save it to the Database')
            ->addArgument('filename', InputArgument::OPTIONAL, 'Argument description', "result.txt")
            ->setHelp("This command allows you to create users...")

        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filePath = $input->getArgument('filename');
        $reader = $this->getContainer()->get('loki_tuo_result.reader');
        $count = $reader->readFile($filePath);

        $output->writeln('Persisted '.$count. " Results");
    }

}
