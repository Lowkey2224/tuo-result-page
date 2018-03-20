<?php

namespace App\LokiTuoResultBundle\Service\RabbitMq;


use App\LokiTuoResultBundle\Entity\BattleLog;
use App\LokiTuoResultBundle\Entity\Player;
use App\LokiTuoResultBundle\Service\QueueItem\Service as QueueItemManager;
use App\LokiTuoResultBundle\Service\TyrantApiConnector\Service;
use Doctrine\ORM\EntityManager;
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
        $result = [];
        try {
            $result = $this->connector->battleAllBattles($this->player);
        } catch (\Exception $exception) {
            $this->logger->error("Failed to battle for Player " . $this->player->getName());
            $this->logger->error($exception->getMessage());
            $this->logger->error($exception->getTraceAsString());
            return true;
        } finally {
            $this->queueItemManager->setStatusFinished($queueItem);
        }

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
        //Dont send a message if no Battles were fought
        if (count($result) > 0) {
            $battleLog = new BattleLog();
            $battleLog->setPlayer($this->player)
                ->setWon($won)
                ->setBattles(count($result))
                ->setGold($gold)
                ->setRating($rating)
                ->setStatusUnread();
            $this->em->persist($battleLog);
            $this->em->flush();
        }

        return true;
    }
}
