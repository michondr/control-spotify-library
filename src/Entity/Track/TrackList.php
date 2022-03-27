<?php

declare(strict_types = 1);

namespace App\Entity\Track;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @extends \Doctrine\Common\Collections\ArrayCollection<int, Track>
 * @method Track[] getIterator
 */
class TrackList extends ArrayCollection
{
    public function hasTrackBySpotifyId(string $spotifyId): bool
    {
        return $this->exists(fn($key, Track $item) => $item->getSpotifyId() === $spotifyId);
    }

    public function getSpotifyIds(): array
    {
        return array_map(
            fn(Track $t) => $t->getSpotifyId(),
            $this->toArray()
        );
    }

}