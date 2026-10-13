<?php

namespace App\Repositories;

use App\Models\Budget;
use App\Repositories\Interfaces\BudgetRepositoryInterface;
use Illuminate\Support\Collection;

class BudgetRepository implements BudgetRepositoryInterface
{
    public function getForMonth(int $userId, int $month, int $year): Collection
    {
        return Budget::query()
            ->with('category')
            ->where('user_id', $userId)
            ->where('month', $month)
            ->where('year', $year)
            ->orderBy('category_id')
            ->get();
    }

    public function create(array $data): Budget
    {
        return Budget::create($data);
    }
}
