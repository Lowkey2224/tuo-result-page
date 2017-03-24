<?php

namespace LokiUserBundle\Security;

use LokiUserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserVoter extends Voter
{
    const EDIT   = 'edit';
    const VIEW   = 'view';
    const DELETE = 'delete';

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
        if (!in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])) {
            return false;
        }

        // only vote on Post objects inside this voter
        if (!$subject instanceof User) {
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

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }
        if ($user->hasRole('ROLE_SUPERADMIN')) {
            return true;
        }

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($subject, $user);
            case self::EDIT:
                return $this->canEdit($subject, $user);
            case self::DELETE:
                return $this->canDelete($subject, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canView(User $subject, User $authUser)
    {
        return $authUser->hasRole('ROLE_ADMIN')
            || $authUser == $subject
            || ($subject->isEnabled() && $authUser->hasRole('ROLE_MODERATOR'));
    }

    private function canEdit(User $subject, User $authUser)
    {
        return $this->canView($subject, $authUser) || $authUser->$authUser->hasRole('ROLE_MODERATOR');
    }

    private function canDelete(User $subject, User $authUser)
    {
        return $authUser->hasRole('ROLE_ADMIN');
    }
}
