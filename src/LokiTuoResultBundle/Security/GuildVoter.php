<?php

namespace LokiTuoResultBundle\Security;

use LokiTuoResultBundle\Entity\Guild;
use LokiUserBundle\Entity\User;

class GuildVoter extends AbstractVoter
{
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
        $correctRole = $user->hasRole('ROLE_MODERATOR')
            || $user->hasRole('ROLE_ADMIN');

        return $correctRole;
    }
}
