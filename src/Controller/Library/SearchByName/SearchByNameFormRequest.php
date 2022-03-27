<?php

declare(strict_types = 1);

namespace App\Controller\Library\SearchByName;

class SearchByNameFormRequest
{
    public string $query;

    public function getQuery(): string
    {
        return $this->query;
    }
}