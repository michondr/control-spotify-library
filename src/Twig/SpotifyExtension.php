<?php

declare(strict_types = 1);

namespace App\Twig;

use App\Spotify\Exception\SpotifyNeedsAuthorizationException;
use App\Spotify\SpotifyRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SpotifyExtension extends AbstractExtension
{

    public function __construct(
        private SpotifyRepository $spotifyRepository
    )
    {
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('getPlaylists', [$this, 'getPlaylists']),
            new TwigFunction('getUserInfo', [$this, 'getUserInfo']),
        ];
    }

    public function getPlaylists(): array
    {
        try {
            return $this->spotifyRepository->getPlaylists();
        } catch (SpotifyNeedsAuthorizationException) {
            return [];
        }
    }

    public function getUserInfo(): object
    {
        try {
            return $this->spotifyRepository->getUserInfo();
        } catch (SpotifyNeedsAuthorizationException) {
            return new \StdClass;
        }
    }
}
