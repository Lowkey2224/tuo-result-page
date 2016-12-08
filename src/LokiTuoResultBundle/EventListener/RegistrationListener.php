<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 15.11.16
 * Time: 16:47
 */

namespace LokiTuoResultBundle\EventListener;

use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RegistrationListener implements EventSubscriberInterface
{

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
        return array(
            FOSUserEvents::REGISTRATION_CONFIRM => 'onRegistrationConfirm',
        );
    }

    public function onRegistrationConfirm(FormEvent $event)
    {
        var_dump($event);
        die();
    }
}