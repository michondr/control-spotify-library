<?php

declare(strict_types = 1);

namespace App\Entity\User;

use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\SecurityEvents;

class UserAuthenticator
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private EventDispatcherInterface $eventDispatcher
    )
    {
    }

    public function authenticateUser(User $user)
    {
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $this->tokenStorage->setToken($token);

        $event = new SecurityEvents();
        $this->eventDispatcher->dispatch($event, SecurityEvents::INTERACTIVE_LOGIN);
    }
}