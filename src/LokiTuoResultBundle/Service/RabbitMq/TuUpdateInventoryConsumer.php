<?php

namespace LokiTuoResultBundle\Service\RabbitMq;

use Doctrine\ORM\EntityManager;
use LokiTuoResultBundle\Entity\Player;
use LokiTuoResultBundle\Service\OwnedCards\Service as OcManager;
use LokiTuoResultBundle\Service\TyrantApiConnector\Service as Connector;
use OldSound\RabbitMqBundle\RabbitMq\BatchConsumerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class TuUpdateInventoryConsumer implements BatchConsumerInterface, ConsumerInterface
{
    /**
     * @var EntityManager
     */
    protected $em;
    /**
     * @var Connector
     */
    protected $connector;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /** @var OcManager */
    private $ocManager;
    private $player;

    public function __construct(
        EntityManager $entityManager,
        Connector $connector,
        LoggerInterface $logger,
        OcManager $ocManager
    ) {
        $this->ocManager = $ocManager;
        $this->em = $entityManager;
        $this->connector = $connector;
        $this->logger = $logger;
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
        $this->player = $this->em->find(Player::class, $playerId);
        if (!$this->player->hasKongCredentials()) {
            $this->player = null;
            return true;
        }
        $idAmountMap = $this->connector->getInventoryAndDeck($this->player);
        if (!empty($idAmountMap)) {
            $this->ocManager->removeOldOwnedCardsForPlayer($this->player);
            $ocs = $this->ocManager->persistOwnedCardsByTuoId($idAmountMap, $this->player);
            $this->logger->info(sprintf("Persisted %d cards for player %s", count($ocs), $this->player->getName()));
        }
        return true;
    }
}
