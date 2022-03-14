<?php

declare(strict_types = 1);

namespace App\Entity\Tag;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TagRepository::class)]
class Tag
{

}