<?php

declare(strict_types = 1);

namespace App\Entity\CacheItem;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CacheItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CacheItem::class);
    }

    public function save(CacheItem $tag): CacheItem
    {
        $this->getEntityManager()->persist($tag);
        $this->getEntityManager()->flush();

        return $tag;
    }
}