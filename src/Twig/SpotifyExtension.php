<?php

declare(strict_types = 1);

namespace App\Twig;

use App\Spotify\Exception\SpotifyNeedsAuthorizationException;
use App\Spotify\SpotifyRepository;
use App\Spotify\SpotifyRepositoryCacheManager;
use Psr\Log\LoggerInterface;
use SpotifyWebAPI\SpotifyWebAPIException;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SpotifyExtension extends AbstractExtension
{

    public function __construct(
        private SpotifyRepository $spotifyRepository,
        private LoggerInterface $logger,
        private SpotifyRepositoryCacheManager $spotifyRepositoryCacheManager,
    )
    {
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('getPlaylists', [$this, 'getPlaylists']),
            new TwigFunction('getTrack', [$this, 'getTrack']),
            new TwigFunction('getUserInfo', [$this, 'getUserInfo']),
            new TwigFunction('getCurrentPlayback', [$this, 'getCurrentPlayback']),
            new TwigFunction('isUserLoggedInSpotify', [$this, 'isUserLoggedInSpotify']),
        ];
    }

    public function isUserLoggedInSpotify(): bool
    {
        try {
            $this->spotifyRepository->getUserInfo();

            return true;
        } catch (SpotifyNeedsAuthorizationException) {
            //pass
        } catch (SpotifyWebAPIException $e) {
            if ($e->hasExpiredToken() === false) {
                $this->logger->critical('unexpected response from spotify with auth token', ['exception' => $e]);
            }
        }

        return false;
    }

    public function getPlaylists(): array
    {
        try {
            return $this->spotifyRepository->getPlaylists();
        } catch (\Exception $e) {
            $this->logger->error('cannot getPlaylists', ['exception' => $e]);
        }

        return [];
    }

    public function getTrack(string $spotifyId): ?object
    {
        try {
            return $this->spotifyRepositoryCacheManager->getTrack($spotifyId);
        } catch (\Exception $e) {
            $this->logger->error('cannot getTrack', ['exception' => $e]);
        }

        return null;
    }

    public function getUserInfo(): ?object
    {
        try {
            return $this->spotifyRepository->getUserInfo();
        } catch (\Exception $e) {
            $this->logger->error('cannot getUserInfo', ['exception' => $e]);
        }

        return null;
    }

    public function getCurrentPlayback(): ?object
    {
        try {
            return $this->spotifyRepository->getCurrentPlayback();
        } catch (\Exception $e) {
            $this->logger->error('cannot getCurrentPlayback', ['exception' => $e]);
        }

        return null;
    }
}
