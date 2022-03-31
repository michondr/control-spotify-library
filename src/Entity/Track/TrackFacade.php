<?php

namespace App\Entity\Track;

use App\Entity\CacheItem\CacheItem;
use App\Entity\CacheItem\CacheItemRepository;
use App\Entity\Tag\Tag;
use App\Entity\Tag\TagList;
use App\Entity\User\UserProvider;
use SpotifyWebAPI\SpotifyWebAPI;

class TrackFacade
{
    public function __construct(
        private TrackRepository $trackRepository,
        private UserProvider $userProvider,
        private SpotifyWebAPI $spotifyWebAPI,
        private CacheItemRepository $cacheItemRepository
    )
    {
    }

    public function saveSpotifyTrack(object $spotifyTrack)
    {
        $track = $this->trackRepository->findById($spotifyTrack->id);

        if ($track === null) {
            $track = new Track(
                $spotifyTrack->name,
                $spotifyTrack->id
            );
        }

        $cacheData = new CacheItem($spotifyTrack, new \DateTimeImmutable('+ 1 month'));
        $this->cacheItemRepository->save($cacheData);
        $track->setCacheData($cacheData);

        return $this->trackRepository->save($track);
    }

    public function getTrackListByNameMatchingQuery(string $query): TrackList
    {
        return $this->trackRepository->getByNameQueryForUser(
            $this->userProvider->getUser(),
            $query
        );
    }

    public function getTrackIfNotExists(string $spotifyId): Track
    {
        $track = $this->trackRepository->findById($spotifyId);

        if ($track === null) {
            $spotifyTrack = $this->spotifyWebAPI->getTrack($spotifyId);

            $track = $this->saveSpotifyTrack($spotifyTrack);
        }

        return $track;
    }

    /**
     * @param TagList $tagList
     * @return TrackList
     *
     * optimize to:
     *
     *  SELECT tag_to_track.track_id, COUNT(tag_to_track.tag_id)
     * FROM tag_to_track
     * JOIN tag ON tag.id = tag_to_track.tag_id
     * JOIN track ON tag_to_track.track_id = track.id
     * WHERE tag.name IN ('elektro', 'pop', 'rock')
     * GROUP BY tag_to_track.track_id
     * HAVING COUNT(tag_to_track.tag_id) = 3
     */
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
