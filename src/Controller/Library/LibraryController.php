<?php

declare(strict_types = 1);

namespace App\Controller\Library;

use App\Entity\Tag\TagFacade;
use App\Entity\Tag\TagRepository;
use App\Entity\Track\TrackRepository;
use App\Spotify\SpotifyRepository;
use App\Spotify\SpotifyRepositoryCacheManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

class LibraryController extends AbstractController
{

    public function __construct(
        private TagFacade $tagFacade,
        private TagRepository $tagRepository,
        private SpotifyRepositoryCacheManager $spotifyRepositoryCacheManager
    )
    {
    }

    #[Route(path: '/tags', name: 'tags')]
    public function listTagAction(): Response
    {
        return $this->render(
            'tag/list.html.twig',
            [
                'tags' => $this->tagFacade->getUserTags(),
            ]
        );
    }

    #[Route(path: '/tags/{id}', name: 'tags.detail')]
    public function detailTagAction(string $id): Response
    {
        $tag = $this->tagRepository->find(Uuid::fromString($id));

        if ($tag === null) {
            throw new NotFoundHttpException();
        }

        return $this->render(
            'tag/detail.html.twig',
            [
                'tag' => $tag,
            ]
        );
    }

    #[Route(path: '/track/{trackId}', name: 'track')]
    public function listSongAction(string $trackId): Response
    {
        return $this->render(
            'library/track.html.twig',
            [
                'track' => $this->spotifyRepositoryCacheManager->getTrack($trackId),
            ]
        );
    }

}
