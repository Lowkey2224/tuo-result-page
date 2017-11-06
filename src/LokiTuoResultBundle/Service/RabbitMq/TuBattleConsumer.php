<?php

namespace LokiTuoResultBundle\Service\RabbitMq;


use Doctrine\ORM\EntityManager;
use LokiTuoResultBundle\Entity\Player;
use LokiTuoResultBundle\Service\QueueItem\Service as QueueItemManager;
use LokiTuoResultBundle\Service\TyrantApiConnector\Service;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class TuBattleConsumer implements ConsumerInterface
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Service
     */
    protected $connector;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Player the player this queue Item handles
     */
    protected $player;

    protected $result = null;

    /** @var QueueItemManager */
    protected $queueItemManager;

    public function __construct(
        EntityManager $entityManager,
        Service $connector,
        LoggerInterface $logger,
        QueueItemManager $service
    ) {
        $this->em = $entityManager;
        $this->connector = $connector;
        $this->logger = $logger;
        $this->queueItemManager = $service;
    }

    /**
     * @inheritdoc
     */
    public function execute(AMQPMessage $msg)
    {
        $body = $msg->getBody();
        $body = unserialize($body);
        $playerId = $body['playerId'];
        $queueItem = $this->queueItemManager->getItem($body['queueItemId']);
        $this->queueItemManager->setStatusRunning($queueItem);
        $this->player = $this->em->find(Player::class, $playerId);
        if (!$this->player->hasKongCredentials()) {
            $this->player = null;
            $this->queueItemManager->setStatusFinished($queueItem);
            return true;
        }


        $this->connector->battleAllBattles($this->player);

        $this->queueItemManager->setStatusFinished($queueItem);
        return true;
    }
}
