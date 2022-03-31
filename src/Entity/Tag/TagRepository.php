<?php

namespace App\Entity\Tag;

use App\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;
use Webmozart\Assert\Assert;

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

    public function remove(Tag $tag)
    {
        $this->getEntityManager()->remove($tag);
        $this->getEntityManager()->flush();
    }

    public function getById(Uuid $id): Tag
    {
        $tag = $this->find($id);
        Assert::notNull($tag);

        return $tag;
    }

    public function findByNameAndUser(string $name, User $owner): ?Tag
    {
        return $this->findOneBy([
            'name' => $name,
            'owner' => $owner,
        ]);
    }

    public function findAllByUser(User $owner): TagList
    {
        return new TagList(
            $this->findBy(['owner' => $owner])
        );
    }

}
