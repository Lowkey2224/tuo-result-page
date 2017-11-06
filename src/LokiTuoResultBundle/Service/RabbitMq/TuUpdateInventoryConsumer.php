<?php

namespace LokiTuoResultBundle\Service\RabbitMq;

use Doctrine\ORM\EntityManager;
use LokiTuoResultBundle\Entity\Player;
use LokiTuoResultBundle\Service\OwnedCards\Service as OcManager;
use LokiTuoResultBundle\Service\QueueItem\Service as QueueItemManager;
use LokiTuoResultBundle\Service\TyrantApiConnector\Service as Connector;
use OldSound\RabbitMqBundle\RabbitMq\BatchConsumerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class TuUpdateInventoryConsumer implements BatchConsumerInterface, ConsumerInterface
{
    /** @var EntityManager */
    protected $em;

    /** @var Connector */
    protected $connector;

    /** @var LoggerInterface */
    protected $logger;

    /** @var OcManager */
    private $ocManager;

    /** @var QueueItemManager */
    protected $queueItemManager;
    
    public function __construct(
        EntityManager $entityManager,
        Connector $connector,
        LoggerInterface $logger,
        OcManager $ocManager,
        QueueItemManager $service
    ) {
        $this->ocManager = $ocManager;
        $this->em = $entityManager;
        $this->connector = $connector;
        $this->logger = $logger;
        $this->queueItemManager = $service;
    }

    /**
     * @inheritdoc
     */
    public function batchExecute(array $messages)
    {
        $this->logger->info("Doing Batch Execution");
        $result = [];
        foreach ($messages as $message) {
            $this->execute($message);
            $result[(int)$message->delivery_info['delivery_tag']] = true;
        }
        return $result;
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
        $player = $this->em->find(Player::class, $playerId);
        if (!$player->hasKongCredentials()) {
            $player = null;
            $this->queueItemManager->setStatusFinished($queueItem);
            return true;
        }
        $idAmountMap = $this->connector->getInventoryAndDeck($player);
        if (!empty($idAmountMap)) {
            $this->ocManager->removeOldOwnedCardsForPlayer($player);
            $ocs = $this->ocManager->persistOwnedCardsByTuoId($idAmountMap, $player);
            $this->logger->info(sprintf("Persisted %d cards for player %s", count($ocs), $player->getName()));
        }
        $this->queueItemManager->setStatusFinished($queueItem);
        return true;
    }
}
