<?php

namespace LokiTuoResultBundle\Security;

use LokiTuoResultBundle\Entity\Player;
use LokiUserBundle\Entity\User;

class PlayerVoter extends AbstractVoter
{
    const VIEW = 'view';
    const EDIT = 'edit';

    protected function canView(Player $player, User $user)
    {
        return $player->isActive() || $user->hasRole('ROLE_ADMIN');
    }

    protected function canEdit(Player $player, User $user)
    {
        $correctRole = $user->hasRole('ROLE_MODERATOR')
            || $user->hasRole('ROLE_ADMIN')
            || $user->hasRole('ROLE_SUPER_ADMIN');

        return $correctRole || $player->isOwnedBy($user);
    }

    /**
     * Returns all attributes that will be supported.
     *
     * @return string[]
     */
    protected function getAttributes(): array
    {
        return [self::VIEW, self::EDIT];
    }

    /**
     * returns the class name of the supported class.
     *
     * @return string[]
     */
    protected function getEntityClass(): array
    {
        return [Player::class];
    }

    /**
     * Returns an Array with attribute => MethodToCall.
     *
     * @return array
     */
    protected function getAttributeMethodMap(): array
    {
        return [
            self::VIEW => 'canView',
            self::EDIT => 'canEdit',
        ];
    }
}
