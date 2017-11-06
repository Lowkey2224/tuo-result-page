<?php

namespace LokiTuoResultBundle\Service\RabbitMq;

use Doctrine\ORM\EntityManager;
use LokiTuoResultBundle\Entity\Player;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;

class TuApiProducer
{
    /** @var ProducerInterface */
    private $entityProducer;

    /** @var EntityManager */
    private $em;

    public function __construct(ProducerInterface $entityProducer, EntityManager $entityManager)
    {
        $this->em = $entityManager;
        $this->entityProducer = $entityProducer;
    }

    public function updatePlayerInventories(Player $player)
    {
        $arr = [
            'playerId' => $player->getId(),
            'method' => 'updateInventory'
        ];
        $msg = serialize($arr);
        $this->entityProducer->publish($msg);
    }

    public function battleAllBatles(Player $player)
    {
        $arr = [
            'playerId' => $player->getId(),
            'method' => 'battleAll'
        ];
        $msg = serialize($arr);
        $this->entityProducer->publish($msg);
    }

}
