<?php

namespace App\Entity\Track;

use App\Entity\User\User;
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

    public function getByNameQueryForUser(User $user, string $query): TrackList
    {
        $tracks = $this->createQueryBuilder('track')
            ->join('track.tags', 'tag')
            ->join('tag.owner', 'owner')
            ->andWhere('track.name LIKE :query')->setParameter('query', '%' . $query . '%')
            ->andWhere('owner.id = :currentUserId')->setParameter('currentUserId', $user->getId(), 'uuid')
            ->getQuery()
            ->getResult();

        return new TrackList($tracks);
    }
}
