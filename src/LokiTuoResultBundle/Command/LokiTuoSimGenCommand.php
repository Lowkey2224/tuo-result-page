<?php

namespace LokiTuoResultBundle\Command;

use LokiTuoResultBundle\Entity\CardFile;
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
        $repo = $em->getRepository('LokiTuoResultBundle:CardFile');
        $cardFiles = $repo->getAllIds();
        $res = [];
        /** @var CardFile $file */
        foreach ($cardFiles as $file) {
            $file = $repo->find($file['id']);
            $hash = md5($file->getContent());
            $file->setChecksum($hash);
            $em->persist($file);

        }
        $em->flush();
        $output->writeln("Updated ".count($cardFiles). " Files");
        $removeCount = 0;
        foreach ($res as $checksum => $fileIds)
        {
            if(count($fileIds)==1)
            {
                continue;
            }
            for ($i=0; $i < count($fileIds); $i++)
            {
                $cardFile = $repo->find($fileIds[$i]);
                $output->writeln("File has ".count($cardFile->getCards()). " Cards");
//                $em->remove($cardFile);
                $removeCount++;
            }
        }
        $em->flush();
        $output->writeln("Removed $removeCount files because they had the Same checksum");
    }
}
