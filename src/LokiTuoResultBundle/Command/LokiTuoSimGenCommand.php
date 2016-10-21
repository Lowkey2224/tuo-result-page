<?php

namespace LokiTuoResultBundle\Command;

use LokiTuoResultBundle\Entity\Player;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LokiTuoSimGenCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('loki:tuo:sim:gen')
            ->setDescription('...');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $repo = $em->getRepository('LokiTuoResultBundle:Player');
        $players = $repo->findAll();
        /** @var Player $player */
        foreach ($players as $player) {
            $g = $player->getGuild();
            $player->setCurrentGuild($g);
            $em->persist($player);
        }
        $em->flush();
        $output->writeln('Command result.');
    }
}
