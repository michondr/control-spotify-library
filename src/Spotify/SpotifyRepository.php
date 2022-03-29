<?php

declare(strict_types = 1);

namespace App\Spotify;

use App\Spotify\Exception\SpotifyNeedsAuthorizationException;
use JetBrains\PhpStorm\ArrayShape;
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

    public function getCurrentPlayback(): ?object
    {
        $this->authenticateApi();

        return $this->api->getMyCurrentPlaybackInfo();
    }

    public function getPlaylists(): array
    {
        $this->authenticateApi();

        $playlists = $this->api->getMyPlaylists(['limit' => 50])->items;
        usort($playlists, fn($a, $b) => $a->name <=> $b->name);

        return $playlists;
    }

    public function getPlaylist(string $playlistId): object
    {
        $this->authenticateApi();

        return $this->api->getPlaylist($playlistId, ['limit' => 200]);
    }

    public function getPlaylistTracks(string $playlistId): array
    {
        $this->authenticateApi();
        $playlistTracks = [];
        $offset = 0;

        while (true) {
            $playlistTracksResponse = $this->api->getPlaylistTracks($playlistId, ['offset' => $offset]);
            $playlistTracks = array_merge($playlistTracks, $playlistTracksResponse->items);

            if ($playlistTracksResponse->total > count($playlistTracks)) {
                $offset += count($playlistTracksResponse->items);
            } else {
                break;
            }
        }

        return $playlistTracks;
    }

    public function getTrack(string $songId): object
    {
        $this->authenticateApi();

        return $this->api->getTrack($songId);
    }

    private function authenticateApi()
    {
        if ($this->requestStack->getMainRequest() !== null) {
            $accessToken = $this->requestStack->getSession()->get(self::SPOTIFY_ACCESS_TOKEN);
            $refreshToken = $this->requestStack->getSession()->get(self::SPOTIFY_REFRESH_TOKEN);

            if (is_string($accessToken) === false) {
                throw new SpotifyNeedsAuthorizationException();
            }

            $this->api->setAccessToken($accessToken);
        }
    }
}
