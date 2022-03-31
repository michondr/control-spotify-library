<?php

declare(strict_types = 1);

namespace App\Entity\Track;

use App\Entity\CacheItem\CacheItem;
use App\Entity\Tag\Tag;
use App\Entity\Tag\TagList;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: TrackRepository::class)]
class Track
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\Column(type: 'string', unique: true)]
    private string $spotifyId;

    #[ORM\Column(type: 'string')]
    private string $name;

    #[ORM\ManyToMany(targetEntity: Tag::class, mappedBy: 'tracks')]
    private Collection $tags;

    #[ORM\OneToOne(targetEntity: CacheItem::class, fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(referencedColumnName: 'id')]
    private ?CacheItem $cacheData;

    public function __construct(
        string $name,
        string $spotifyId,
        ?CacheItem $endpointData = null,
    )
    {
        $this->id = Uuid::v6();
        $this->spotifyId = $spotifyId;
        $this->name = $name;
        $this->tags = new TagList([]);
        $this->cacheData = $endpointData;
    }

    public function getId(): \Symfony\Component\Uid\UuidV6|Uuid
    {
        return $this->id;
    }

    public function getSpotifyId(): string
    {
        return $this->spotifyId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTags(): TagList
    {
        return new TagList($this->tags->toArray());
    }

    public function getCacheData(): ?CacheItem
    {
        return $this->cacheData;
    }

    public function setCacheData(CacheItem $cacheData): void
    {
        $this->cacheData = $cacheData;
    }
}