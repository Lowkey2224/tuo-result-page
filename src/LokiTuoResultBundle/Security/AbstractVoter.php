<?php


namespace LokiTuoResultBundle\Security;

use LokiUserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

abstract class AbstractVoter extends Voter
{

    /**
     * returns the class name of the supported class
     * @return string[]
     */
    abstract protected function getEntityClass(): array;

    /**
     * Returns an Array with attribute => MethodToCall
     * @return array
     */
    abstract protected function getAttributeMethodMap(): array;

    /**
     * Determines if the attribute and subject are supported by this voter.
     *
     * @param string $attribute An attribute
     * @param mixed $subject The subject to secure, e.g. an object the user wants to access or any other PHP type
     *
     * @return bool True if the attribute and subject are supported, false otherwise
     */
    protected function supports($attribute, $subject)
    {

        $map = $this->getAttributeMethodMap();
        if (!isset($map[$attribute])) {
            return false;
        }

        $supports = false;
        foreach ($this->getEntityClass() as $class) {
            $supports = !$subject instanceof $class ?: true;
        }

        return $supports;
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string $attribute
     * @param mixed $subject
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {

        $user = $token->getUser();

        if($user->hasRole('ROLE_SUPER_ADMIN'))

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }
        $map = $this->getAttributeMethodMap();
        if (isset($map[$attribute])) {
            $method = $map[$attribute];
            return $this->$method($subject, $user);
        }

        throw new \LogicException(sprintf('Attribute %s not defined on Voter %s', $attribute, get_class($this)));
    }
}
