<?php

namespace LokiTuoResultBundle\Security;

use LokiTuoResultBundle\Entity\Player;
use LokiUserBundle\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PlayerVoter extends Voter
{
    /** @var LoggerInterface $logger */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    const VIEW = 'view';
    const EDIT = 'edit';

    /**
     * Determines if the attribute and subject are supported by this voter.
     *
     * @param string $attribute An attribute
     * @param mixed  $subject   The subject to secure, e.g. an object the user wants to access or any other PHP type
     *
     * @return bool True if the attribute and subject are supported, false otherwise
     */
    protected function supports($attribute, $subject)
    {
        if (! in_array($attribute, [self::VIEW, self::EDIT])) {
            return false;
        }

        if (! $subject instanceof Player) {
            return false;
        }

        return true;
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string         $attribute
     * @param mixed          $subject
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (! $user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        // you know $subject is a Post object, thanks to supports
        /** @var Player $player */
        $player = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($player, $user);
            case self::EDIT:
                return $this->canEdit($player, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

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
}
