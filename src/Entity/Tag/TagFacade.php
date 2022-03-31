<?php

namespace App\Entity\Tag;

use App\Entity\Track\TrackRepository;
use App\Entity\User\UserProvider;

class TagFacade
{
    public function __construct(
        private TagRepository $tagRepository,
        private UserProvider $userProvider,
        private TrackRepository $trackRepository
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

    public function getUserTagsForTrack(string $trackSpotifyId): TagList
    {
        $user = $this->userProvider->getUser();
        $track = $this->trackRepository->findById($trackSpotifyId);

        return $track->getTags()->filter(
            fn(Tag $tag) => $tag->getOwner()->getId()->equals($user->getId())
        );
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

    public function createFreshFromPlaylists(array $playlists)
    {
        $user = $this->userProvider->getUser();
        $allUserTags = $this->getUserTags();

        $tags = [];

        foreach ($playlists as $playlist) {
            if ($allUserTags->exists(fn($key, Tag $t) => $t->getName() === $playlist->name) === false) {
                $tags[] = new Tag($playlist->name, $user);
            }
        }

        $this->tagRepository->saveTagList(new TagList($tags));
    }
}
