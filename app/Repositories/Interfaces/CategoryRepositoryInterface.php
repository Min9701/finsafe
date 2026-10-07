<?php

namespace App\Repositories\Interfaces;

use Illuminate\Support\Collection;

interface CategoryRepositoryInterface
{
    public function getForUser(?int $userId): Collection;

    public function getForUserOrdered(?int $userId): Collection;
}
