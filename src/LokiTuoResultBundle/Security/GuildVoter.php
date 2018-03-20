<?php

namespace App\LokiTuoResultBundle\Security;

use App\LokiTuoResultBundle\Entity\Guild;
use LokiUserBundle\Entity\User;

class GuildVoter extends AbstractVoter
{
    const VIEW = 'view.guild';
    const EDIT = 'edit.guild';

    /**
     * returns the class name of the supported class.
     *
     * @return string[]
     */
    protected function getEntityClass(): array
    {
        return [Guild::class];
    }

    /**
     * Returns an Array with attribute => MethodToCall.
     *
     * @return array
     */
    protected function getAttributeMethodMap(): array
    {
        return [
            self::EDIT => 'canEdit',
            self::VIEW => 'canView',
        ];
    }

    protected function canView(Guild $guild, User $user)
    {
        return $guild->isEnabled() || $this->canView($guild, $user);
    }

    protected function canEdit(Guild $guild, User $user)
    {
        return $user->hasRole('ROLE_MODERATOR');
    }
}
