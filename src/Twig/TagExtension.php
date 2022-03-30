<?php

declare(strict_types = 1);

namespace App\Twig;

use App\Entity\Tag\TagFacade;
use App\Entity\Tag\TagList;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TagExtension extends AbstractExtension
{

    public function __construct(
        private TagFacade $tagFacade,
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
        return $this->tagFacade->getUserTagsForTrack($trackSpotifyId);
    }

}
