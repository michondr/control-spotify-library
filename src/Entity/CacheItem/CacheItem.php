<?php

declare(strict_types = 1);

namespace App\Entity\CacheItem;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity()]
class CacheItem
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\Column(type: 'string', length: 10000)]
    private string $storedData;

    #[ORM\Column(type: 'datetimetz_immutable')]
    private \DateTimeImmutable $expiresAt;

    public function __construct(
        object $storedData,
        \DateTimeImmutable $expiresAt
    )
    {
        $this->id = Uuid::v6();
        $this->storedData = json_encode($storedData);
        $this->expiresAt = $expiresAt;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getStoredData(): object
    {
        return json_decode($this->storedData);
    }

    public function getExpiresAt(): \DateTimeImmutable
    {
        return $this->expiresAt;
    }

}