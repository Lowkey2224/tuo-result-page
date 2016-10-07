<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 09.09.16
 * Time: 12:51
 */

namespace LokiTuoResultBundle\Service\UserService;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Symfony\Component\Security\Core\User\User;

class Service
{

    use LoggerAwareTrait;
    /**
     * @var array
     */
    private $guilds;

    public function __construct(array $guilds)
    {
        $this->guilds = $guilds;
        $this->logger = new NullLogger();
    }

    public function getGuildsForUser(User $user)
    {
        $roles = $user->getRoles();
        $guilds = [];
        foreach ($roles as $role) {
            foreach ($this->guilds as $guild) {
                if (strpos($role, $guild) !== false) {
                    $guilds[] = $guild;
                }
            }
        }

        return $guilds;
    }

    public function canUserAccess(User $user, $guildNeeded)
    {
        if ($this->isAdmin($user)) {
            return true;
        }
        return in_array($guildNeeded, $this->getGuildsForUser($user));
    }

    private function isAdmin(User $user)
    {
        $roles = $user->getRoles();

        foreach ($roles as $role) {
            if ($role === 'ROLE_ADMIN') {
                return true;
            }
        }
        return false;
    }
}
