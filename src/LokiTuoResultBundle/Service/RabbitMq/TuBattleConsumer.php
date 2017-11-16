<?php

namespace LokiTuoResultBundle\Service\RabbitMq;


use Doctrine\ORM\EntityManager;
use LokiTuoResultBundle\Entity\Message;
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


        $result = $this->connector->battleAllBattles($this->player);
        $won = 0;
        $gold = 0;
        $rating = 0;
        foreach ($result as $resultArray) {
            $gold += $resultArray['gold'];
            $rating += $resultArray['rating'];
            if ($resultArray['rating'] > 0) {
                ++$won;
            }
        }
        $messageText = sprintf("Fought %d battles, won %d, Won %d gold and %d rating", count($result), $won, $gold,
            $rating);
        $msg = new Message();
        $msg->setPlayer($this->player)
            ->setMessage($messageText)
            ->setStatusUnread();
        $this->em->persist($msg);
        $this->em->flush();


        try {
            $this->connector->battleAllBattles($this->player);
        } catch (\Exception $exception) {
            $this->logger->error("Failed to battle for Player " . $this->player->getName());
            $this->logger->error($exception->getMessage());
            $this->logger->error($exception->getTraceAsString());
        }
        $this->queueItemManager->setStatusFinished($queueItem);
        return true;
    }
}
