<?php

declare(strict_types = 1);

namespace App\Twig;

use App\Entity\Tag\TagFacade;
use App\Entity\Tag\TagList;
use App\Entity\Tag\TagRepository;
use App\Entity\Track\TrackRepository;
use App\Entity\User\User;
use App\Entity\User\UserProvider;
use App\Entity\User\UserRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Webmozart\Assert\Assert;

class TagExtension extends AbstractExtension
{

    public function __construct(
        private TagFacade $tagFacade,
        private TagRepository $tagRepository,
        private TrackRepository $trackRepository
    )
    {
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('getUserTags', [$this, 'getUserTags']),
            new TwigFunction('getTrackTags', [$this, 'getTrackTags']),
        ];
    }

    public function getUserTags(): TagList
    {
        return $this->tagFacade->getUserTags();
    }

    public function getTrackTags(string $trackSpotifyId): TagList
    {
        $track = $this->trackRepository->findById($trackSpotifyId);

        Assert::notNull($track);

        return $track->getTags();
    }

}
