<?php

namespace App\Entity\Tag;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }

    public function save(Tag $tag): Tag
    {
        $this->getEntityManager()->persist($tag);
        $this->getEntityManager()->flush();

        return $tag;
    }
}
