<?php

namespace LokiUserBundle\Event;

use LokiUserBundle\Entity\User;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;

class RegistrationCompleteEvent extends Event
{
    /**
     * @var null|Request
     */
    protected $request;

    /**
     * @var User
     */
    protected $user;

    /**
     * UserEvent constructor.
     *
     * @param UserInterface $user
     * @param Request|null  $request
     */
    public function __construct(User $user, Request $request = null)
    {
        $this->user    = $user;
        $this->request = $request;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }
}
