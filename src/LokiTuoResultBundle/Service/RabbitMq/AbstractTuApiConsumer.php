<?php

namespace LokiTuoResultBundle\Service\RabbitMq;

use Doctrine\ORM\EntityManager;
use LokiTuoResultBundle\Entity\Player;
use LokiTuoResultBundle\Service\TyrantApiConnector\Service;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

abstract class AbstractTuApiConsumer implements ConsumerInterface
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

    public function __construct(EntityManager $entityManager, Service $connector, LoggerInterface $logger)
    {
        $this->em = $entityManager;
        $this->connector = $connector;
        $this->logger = $logger;
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
        $this->result = $this->connectorCall();
        $this->afterHook();
        $this->result = null;
        return true;
    }

    /**
     * The Call to the API Connector
     * @return mixed
     */
    abstract protected function connectorCall();

    /**
     * This method is called after the connectorCall and before the returning value<br/>
     * use $this->result and $this->player to access Data
     */
    protected function afterHook()
    {
    }
}
