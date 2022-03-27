<?php

declare(strict_types = 1);

namespace App\Entity\Tag;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @extends \Doctrine\Common\Collections\ArrayCollection<int, Tag>
 * @method Tag[] getIterator
 * @method Tag first()
 */
class TagList extends ArrayCollection
{
    public function hasTagWithName(string $name): bool
    {
        return $this->exists(fn($key, Tag $item) => $item->getName() === $name);
    }
}