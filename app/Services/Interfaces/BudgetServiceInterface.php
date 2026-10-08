<?php

namespace App\Services\Interfaces;

use App\Models\Budget;
use Illuminate\Support\Collection;

interface BudgetServiceInterface
{
    public function getBudgetsForCurrentMonth(int $userId): Collection;

    public function getAvailableExpenseCategories(int $userId): Collection;

    /**
     * @param  array{category_id: int, amount: string}  $data
     */
    public function createBudget(int $userId, array $data): Budget;
}
