<?php

declare(strict_types = 1);

namespace App\Controller\Library\SearchByTags;

use App\Entity\Tag\Tag;
use App\Entity\Tag\TagList;

class SearchByTagsFormRequest
{
    /** @var Tag[] */
    public array $tags;

    public function getTags(): TagList
    {
        return new TagList($this->tags);
    }
}