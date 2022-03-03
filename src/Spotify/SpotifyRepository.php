<?php

declare(strict_types = 1);

namespace App\Spotify;

use App\Spotify\Exception\SpotifyNeedsAuthorizationException;
use SpotifyWebAPI\SpotifyWebAPI;
use Symfony\Component\HttpFoundation\RequestStack;

class SpotifyRepository
{
    public const SPOTIFY_ACCESS_TOKEN = 'accessToken';
    public const SPOTIFY_REFRESH_TOKEN = 'refreshToken';

    public function __construct(
        private SpotifyWebAPI $api,
        private RequestStack $requestStack
    )
    {
    }

    public function getUserInfo(): object
    {
        $this->authenticateApi();

        return $this->api->me();
    }

    public function getPlaylists(): array
    {
        $this->authenticateApi();

        $playlists = $this->api->getMyPlaylists(['limit' => 50])->items;
        usort($playlists, fn($a, $b) => $a->name > $b->name);

        return $playlists;
    }

    private function authenticateApi()
    {
        $accessToken = $this->requestStack->getSession()->get(self::SPOTIFY_ACCESS_TOKEN);
        $refreshToken = $this->requestStack->getSession()->get(self::SPOTIFY_REFRESH_TOKEN);

        if (is_string($accessToken) === false) {
            throw new SpotifyNeedsAuthorizationException();
        }

        $this->api->setAccessToken($accessToken);
    }
}
