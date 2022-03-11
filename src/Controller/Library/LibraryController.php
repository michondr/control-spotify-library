<?php

declare(strict_types = 1);

namespace App\Controller\Library;

use App\Spotify\SpotifyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LibraryController extends AbstractController
{

    public function __construct(
        private SpotifyRepository $spotifyRepository
    )
    {
    }

    #[Route(path: '/library', name: 'library')]
    public function listLibraryAction(): Response
    {
        $playlists = $this->spotifyRepository->getPlaylists();

        return $this->render(
            'library/library.html.twig',
            [
                'playlists' => $playlists,
            ]
        );
    }

    #[Route(path: '/library/playlist/{playlistId}', name: 'library.playlist')]
    public function listPlaylistAction(string $playlistId): Response
    {
        $playlist = $this->spotifyRepository->getPlaylist($playlistId);

        return $this->render(
            'library/playlist.html.twig',
            [
                'playlist' => $playlist,
            ]
        );
    }

    #[Route(path: '/library/track/{trackId}', name: 'library.track')]
    public function listSongAction(string $trackId): Response
    {
        $track = $this->spotifyRepository->getTrack($trackId);

        return $this->render(
            'library/track.html.twig',
            [
                'track' => $track,
            ]
        );
    }

}
