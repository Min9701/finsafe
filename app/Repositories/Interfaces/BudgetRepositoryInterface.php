<?php

namespace App\Repositories\Interfaces;

use App\Models\Budget;
use Illuminate\Support\Collection;

interface BudgetRepositoryInterface
{
    public function getForMonth(int $userId, int $month, int $year): Collection;

    public function create(array $data): Budget;
}
