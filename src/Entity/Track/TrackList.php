<?php

declare(strict_types = 1);

namespace App\Entity\Track;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @extends \Doctrine\Common\Collections\ArrayCollection<int, Track>
 */
class TrackList extends ArrayCollection
{
    public function hasTrackBySpotifyId(string $spotifyId): bool
    {
        return $this->exists(fn($key, Track $item) => $item->getSpotifyId() === $spotifyId);
    }

}