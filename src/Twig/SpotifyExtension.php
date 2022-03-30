<?php

declare(strict_types = 1);

namespace App\Twig;

use App\Spotify\Exception\SpotifyNeedsAuthorizationException;
use App\Spotify\SpotifyRepository;
use SpotifyWebAPI\SpotifyWebAPIException;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SpotifyExtension extends AbstractExtension
{

    public function __construct(
        private SpotifyRepository $spotifyRepository,
        private RequestStack $requestStack
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
        $accessToken = $this->requestStack->getSession()->get(SpotifyRepository::SPOTIFY_ACCESS_TOKEN);

        try {
            $this->spotifyRepository->getUserInfo();
        } catch (SpotifyNeedsAuthorizationException|SpotifyWebAPIException) {
            return false;
        }

        return $accessToken !== null;
    }

    public function getPlaylists(): array
    {
        try {
            return $this->spotifyRepository->getPlaylists();
        } catch (SpotifyNeedsAuthorizationException) {
            return [];
        }
    }

    public function getTrack(string $spotifyId): object
    {
        try {
            return $this->spotifyRepository->getTrack($spotifyId);
        } catch (SpotifyNeedsAuthorizationException) {
            //pass
        } catch (SpotifyWebAPIException $e) {
            if ($e->getMessage() === 'non existing id') {
                //pass
            } else {
                throw $e;
            }
        }

        return new \StdClass;
    }

    public function getUserInfo(): ?object
    {
        try {
            return $this->spotifyRepository->getUserInfo();
        } catch (SpotifyNeedsAuthorizationException) {
            return null;
        }
    }

    public function getCurrentPlayback(): ?object
    {
        try {
            return $this->spotifyRepository->getCurrentPlayback();
        } catch (SpotifyNeedsAuthorizationException) {
            return null;
        }
    }
}
