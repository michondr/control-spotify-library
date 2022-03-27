<?php

namespace App\Entity\Track;

use App\Entity\Tag\Tag;
use App\Entity\Tag\TagList;
use App\Entity\User\User;
use App\Entity\User\UserProvider;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Stopwatch\Stopwatch;

class TrackFacade
{
    public function __construct(
        private TrackRepository $trackRepository,
        private UserProvider $userProvider
    )
    {
    }

    public function getTrackListByNameMatchingQuery(string $query): TrackList
    {
        return $this->trackRepository->getByNameQueryForUser(
            $this->userProvider->getUser(),
            $query
        );
    }


    public function getTrackListByTags(TagList $tagList): TrackList
    {
        $trackList = $tagList->first()->getTracks();

        /** @var Tag $tag */
        foreach ($tagList as $tag) {
            /** @var Track $track */
            foreach ($trackList as $track) {
                if ($track->getTags()->contains($tag) === false) {
                    $trackList->removeElement($track);
                }
            }
        }

        return $trackList;
    }

}
