<?php

namespace LokiTuoResultBundle\Security;

use LokiTuoResultBundle\Model\Message;
use LokiUserBundle\Entity\User;

class MessageVoter extends AbstractVoter
{

    const VIEW = "view.message";

    /**
     * returns the class name of the supported class.
     *
     * @return string[]
     */
    protected function getEntityClass(): array
    {
        return [Message::class];
    }

    protected function canView(Message $message, User $user)
    {
        $owner = $message->getPlayer()->getOwner();
        dump($owner, $user);
        return $owner === $user;
    }

    /**
     * Returns an Array with attribute => MethodToCall.
     *
     * @return array
     */
    protected function getAttributeMethodMap(): array
    {
        return [
            self::VIEW => "canView",
        ];
    }
}
