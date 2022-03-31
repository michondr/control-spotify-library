<?php

declare(strict_types = 1);

namespace App\Spotify;

use App\Entity\User\UserProvider;
use App\Spotify\Exception\SpotifyNeedsAuthorizationException;
use SpotifyWebAPI\SpotifyWebAPI;

class SpotifyRepository
{

    public function __construct(
        private SpotifyWebAPI $api,
        private UserProvider $userProvider
    )
    {
    }

    public function getUserInfo(?string $accessToken = null): object
    {
        $this->authenticateApi($accessToken);

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

    private function authenticateApi(?string $accessToken = null)
    {
        if ($accessToken === null) {
            if ($this->userProvider->isUserLoggedIn() === false) {
                throw new SpotifyNeedsAuthorizationException();
            }

            $accessToken = $this->userProvider->getUser()->getAccessToken();
        }

        $this->api->setAccessToken($accessToken);
    }
}
