<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 09.09.16
 * Time: 12:51
 */

namespace LokiUserBundle\Service\UserService;

use LokiUserBundle\Entity\User;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

class Service
{

    use LoggerAwareTrait;
    /**
     * @var array
     */
    private $guilds;
    /**
     * @var array
     */
    private $registrationCodes;

    public function __construct(array $guilds, array $registrationCodes)
    {
        $this->guilds = $guilds;
        $this->registrationCodes = $registrationCodes;
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
        return true;
        //Now everyone can access
//        return in_array($guildNeeded, $this->getGuildsForUser($user));
    }

    public function isRegistrationCodeValid($code)
    {
        if(empty($this->registrationCodes))
        {
            return true;
        }
        return in_array($code, $this->registrationCodes);
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
