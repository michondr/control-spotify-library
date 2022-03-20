<?php

declare(strict_types = 1);

namespace App\Controller\Library;

use App\Entity\Tag\TagFacade;
use App\Entity\Tag\TagRepository;
use App\Entity\Track\TrackRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;
use Webmozart\Assert\Assert;

class TagController extends AbstractController
{
    public function __construct(
        private TrackRepository $trackRepository,
        private TagRepository $tagRepository,
        private TagFacade $tagFacade,
    )
    {
    }

    #[Route(path: '/track/{trackId}/add-new-tag', name: 'track.add_new_tag')]
    public function addTrackToNewTagAction(Request $request, string $trackId): Response
    {
        $tagName = $request->query->get('tagName');

        $track = $this->trackRepository->findById($trackId);
        $tag = $this->tagFacade->getTagIfNotExists($tagName);

        Assert::notNull($track);
        Assert::notNull($tag);

        $tag->addTrack($track);
        $this->tagRepository->save($tag);

        return $this->getRedirectResponse($request);
    }

    #[Route(path: '/track/{trackId}/add-tag/{tagId}', name: 'track.add_tag')]
    public function addTrackToTagAction(Request $request, string $trackId, string $tagId): Response
    {
        $track = $this->trackRepository->findById($trackId);
        $tag = $this->tagRepository->getById(Uuid::fromString($tagId));

        Assert::notNull($track);
        Assert::notNull($tag);

        $tag->addTrack($track);
        $this->tagRepository->save($tag);

        return $this->getRedirectResponse($request);
    }

    #[Route(path: '/track/{trackId}/remove-tag/{tagId}', name: 'track.remove_tag')]
    public function removeTrackFromTagAction(Request $request, string $trackId, string $tagId): Response
    {
        $track = $this->trackRepository->findById($trackId);
        $tag = $this->tagRepository->getById(Uuid::fromString($tagId));

        Assert::notNull($track);
        Assert::notNull($tag);

        $tag->removeTrack($track);
        $this->tagRepository->save($tag);

        return $this->getRedirectResponse($request);
    }

    private function getRedirectResponse(Request $request): Response
    {
        $referer = $request->headers->get('referer');

        if ($referer !== null) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('track', [
            'trackId' => $request->attributes->get('_route_params')['trackId'],
        ]);
    }

}
