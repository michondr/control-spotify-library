<?php

namespace App\Entity\Tag;

use App\Entity\User\User;
use App\Entity\User\UserProvider;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TagFacade
{
    public function __construct(
        private TagRepository $tagRepository,
        private UserProvider $userProvider,
    )
    {
    }

    public function getUserTags(): TagList
    {
        if ($this->userProvider->isUserLoggedIn()) {
            return $this->tagRepository->findAllByUser($this->userProvider->getUser());
        }

        return new TagList([]);
    }

    public function getTagIfNotExists(string $tagName): Tag
    {
        if ($this->getUserTags()->hasTagWithName($tagName)) {
            return $this->tagRepository->findByNameAndUser($tagName, $this->userProvider->getUser());
        }

        $newTag = new Tag(
            $tagName,
            $this->userProvider->getUser()
        );

        $this->tagRepository->save($newTag);

        return $newTag;
    }
}
