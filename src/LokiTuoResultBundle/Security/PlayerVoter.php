<?php

namespace LokiTuoResultBundle\Security;

use LokiTuoResultBundle\Entity\Player;
use LokiUserBundle\Entity\User;

class PlayerVoter extends AbstractVoter
{
    const VIEW = 'view.player';
    const EDIT = 'edit.player';
    const DELETE = 'delete.player';

    protected function canView(Player $player, User $user)
    {
        return $player->isActive() || $user->hasRole('ROLE_ADMIN');
    }

    protected function canEdit(Player $player, User $user)
    {
        return !$player->isOwnershipConfirmed() || $this->canDelete($player, $user);
    }

    protected function canDelete(Player $player, User $user)
    {
        $correctRole = $user->hasRole('ROLE_MODERATOR')
            || $user->hasRole('ROLE_ADMIN');
        return $correctRole || $player->isOwnedBy($user);
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
            self::DELETE => 'canDelete',
        ];
    }
}
