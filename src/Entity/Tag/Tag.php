<?php

declare(strict_types = 1);

namespace App\Entity\Tag;

use App\Entity\Track\Track;
use App\Entity\Track\TrackList;
use App\Entity\User\User;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/** @ORM\Table (uniqueConstraints={@UniqueConstraint(columns={"name", "owner_id"})}) */
#[ORM\Entity(repositoryClass: TagRepository::class)]
class Tag
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\Column(type: 'string')]
    private string $name;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'owner_id', referencedColumnName: 'id')]
    private User $owner;

    #[ORM\ManyToMany(targetEntity: Track::class, inversedBy: 'tags')]
    #[ORM\JoinTable(name: 'tag_to_track')]
    private Collection $tracks;

    private \DateTimeImmutable $createdAt;

    public function __construct(
        string $name,
        User $owner,
    )
    {
        $this->id = Uuid::v6();
        $this->name = $name;
        $this->owner = $owner;
        $this->createdAt = new \DateTimeImmutable();
        $this->tracks = new TrackList([]);
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getOwner(): User
    {
        return $this->owner;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getTracks(): TrackList
    {
        return new TrackList($this->tracks->toArray());
    }

    public function addTrack(Track $track)
    {
        if ($this->tracks->contains($track)) {
            return;
        }

        $this->tracks->add($track);
    }

    public function removeTrack(Track $track)
    {
        if ($this->tracks->contains($track)) {
            $this->tracks->removeElement($track);
        }
    }

}