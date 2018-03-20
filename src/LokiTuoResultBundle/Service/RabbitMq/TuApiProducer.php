<?php

namespace App\LokiTuoResultBundle\Service\RabbitMq;

use App\LokiTuoResultBundle\Entity\Player;
use App\LokiTuoResultBundle\Service\QueueItem\Service as QueueItemManager;
use Doctrine\ORM\EntityManager;
use LokiUserBundle\Entity\User;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;

class TuApiProducer
{
    /** @var ProducerInterface */
    private $entityProducer;

    /** @var EntityManager */
    private $em;

    /** @var  QueueItemManager */
    private $queueItemManager;

    public function __construct(
        ProducerInterface $entityProducer,
        EntityManager $entityManager,
        QueueItemManager $service
    ) {
        $this->em = $entityManager;
        $this->entityProducer = $entityProducer;
        $this->queueItemManager = $service;
    }

    public function updatePlayerInventories(Player $player, User $user)
    {
        $queueItem = $this->queueItemManager->createItem($user, $player, "update.player.queue.description");
        $arr = [
            'playerId' => $player->getId(),
            'method' => 'updateInventory',
            'queueItemId' => $queueItem->getId(),
        ];
        $msg = serialize($arr);
        $this->entityProducer->publish($msg);
    }

    public function battleAllBatles(Player $player, User $user)
    {
        $queueItem = $this->queueItemManager->createItem($user, $player, "battle.player.queue.description");
        $arr = [
            'playerId' => $player->getId(),
            'method' => 'battleAll',
            'queueItemId' => $queueItem->getId(),
        ];
        $msg = serialize($arr);
        $this->entityProducer->publish($msg);
    }

}
