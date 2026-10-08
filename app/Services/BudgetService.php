<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Category;
use App\Repositories\Interfaces\BudgetRepositoryInterface;
use App\Services\Interfaces\BudgetServiceInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class BudgetService implements BudgetServiceInterface
{
    public function __construct(
        private readonly BudgetRepositoryInterface $budgetRepository,
    ) {}

    public function getBudgetsForCurrentMonth(int $userId): Collection
    {
        return $this->budgetRepository->getForMonth($userId, now()->month, now()->year);
    }

    public function getAvailableExpenseCategories(int $userId): Collection
    {
        return Category::query()
            ->where('type', 'expense')
            ->where(function ($query) use ($userId) {
                $query->whereNull('user_id')->orWhere('user_id', $userId);
            })
            ->orderBy('name')
            ->get();
    }

    public function createBudget(int $userId, array $data): Budget
    {
        $categoryExists = Category::query()
            ->whereKey($data['category_id'])
            ->where('type', 'expense')
            ->where(function ($query) use ($userId) {
                $query->whereNull('user_id')->orWhere('user_id', $userId);
            })
            ->exists();

        if (! $categoryExists) {
            throw ValidationException::withMessages(['category_id' => 'Danh mục không hợp lệ.']);
        }

        try {
            return $this->budgetRepository->create([
                'user_id' => $userId,
                'category_id' => $data['category_id'],
                'month' => now()->month,
                'year' => now()->year,
                'amount' => $data['amount'],
            ]);
        } catch (QueryException $exception) {
            throw ValidationException::withMessages(['category_id' => 'Danh mục này đã có ngân sách trong tháng hiện tại.']);
        }
    }
}
