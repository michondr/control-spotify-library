<?php

declare(strict_types = 1);

namespace App\Spotify;

use App\Entity\Track\TrackFacade;
use App\Entity\Track\TrackRepository;
use Psr\Log\LoggerInterface;

class SpotifyRepositoryCacheManager
{
    public function __construct(
        private SpotifyRepository $spotifyRepository,
        private TrackRepository $trackRepository,
        private TrackFacade $trackFacade,
        private LoggerInterface $logger
    )
    {
    }

    public function getTrack(string $spotifyId): object
    {
        $trackInDb = $this->trackRepository->findById($spotifyId);
        $cachedData = $trackInDb->getCacheData();

        if ($cachedData === null) {
            $spotifyTrack = $this->spotifyRepository->getTrack($spotifyId);

            return $this->trackFacade->saveSpotifyTrack($spotifyTrack);
        }

        $now = new \DateTimeImmutable();
        if ($cachedData->getExpiresAt() < $now) {
            $this->logger->warning('track is in database too long', ['trackId' => $spotifyId]);
        }

        return $cachedData->getStoredData();
    }

}
