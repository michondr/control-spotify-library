<?php

declare(strict_types = 1);

namespace App\Entity\User;

use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Webmozart\Assert\Assert;

class UserProvider
{
    public function __construct(
        private LoggerInterface $logger,
        private UserRepository $userRepository,
        private TokenStorageInterface $tokenStorage
    )
    {
    }

    public function isUserLoggedIn(): bool
    {
        return $this->findUser() !== null;
    }

    public function getUser(): User
    {
        $user = $this->findUser();

        Assert::notNull($user);

        return $user;
    }

    private function findUser(): ?User
    {
        $token = $this->tokenStorage->getToken();

        if ($token === null) {
            return null;
        }

        $user = $token->getUser();

        if ($user === null) {
            $this->logger->info('user is null in token storage');

            return null;
        }

        $userByName = $this->userRepository->findByName($user->getUserIdentifier());

        if ($userByName === null) {
            $this->logger->info('user is null in database storage', ['name' => $user->getUserIdentifier()]);

            return null;
        }

        return $userByName;
    }
}