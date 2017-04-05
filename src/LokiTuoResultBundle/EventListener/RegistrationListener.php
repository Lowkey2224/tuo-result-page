<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 15.11.16
 * Time: 16:47.
 */

namespace LokiTuoResultBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use LokiTuoResultBundle\Entity\Player;
use LokiUserBundle\Event\RegistrationCompleteEvent;
use LokiUserBundle\LokiUserEvents;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RegistrationListener implements EventSubscriberInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(LoggerInterface $logger, EntityManagerInterface $playerRepository)
    {
        $this->logger        = $logger;
        $this->entityManager = $playerRepository;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            LokiUserEvents::REGISTRATION_COMPLETE => 'onRegistrationConfirm',
        ];
    }

    public function onRegistrationConfirm(RegistrationCompleteEvent $event)
    {
        $user = $event->getUser();
        $repo = $this->entityManager->getRepository('LokiTuoResultBundle:Player');
        /** @var Player $player */
        $player = $repo->findOneBy(['name' => $user->getUsername()]);
        // If Player exists claim player.
        if ($player && !$player->getOwner()) {
            $player->setOwner($user);
            $this->entityManager->persist($player);
            $this->entityManager->flush();
//            var_dump("Changed", $player);die();
        }
//        var_dump("not", $player);die();
    }
}
