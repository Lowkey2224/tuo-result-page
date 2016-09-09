<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 09.09.16
 * Time: 12:51
 */

namespace LokiTuoResultBundle\Service\UserService;

use Symfony\Component\Security\Core\User\User;

class Service
{
    /**
     * @var array
     */
    private $guilds;

    public function __construct(array $guilds)
    {
        $this->guilds = $guilds;
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
}
