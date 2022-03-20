<?php

namespace App\Entity\Track;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TrackRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Track::class);
    }

    public function save(Track $track): Track
    {
        $this->getEntityManager()->persist($track);
        $this->getEntityManager()->flush();

        return $track;
    }

    public function findById(string $spotifyId): ?Track
    {
        return $this->findOneBy(['spotifyId' => $spotifyId]);
    }
}
