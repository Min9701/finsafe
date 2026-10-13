<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Category;
use App\Repositories\Interfaces\BudgetRepositoryInterface;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Services\Interfaces\BudgetServiceInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class BudgetService implements BudgetServiceInterface
{
    public function __construct(
        private readonly BudgetRepositoryInterface $budgetRepository,
        private readonly CategoryRepositoryInterface $categoryRepository,
    ) {}

    public function getBudgetsForCurrentMonth(int $userId): Collection
    {
        return $this->budgetRepository->getForMonth($userId, now()->month, now()->year);
    }

    /**
     * Lấy danh mục chi tiêu toàn cục hoặc thuộc người dùng.
     *
     * @return Collection<int, Category>
     */
    public function getAvailableExpenseCategories(int $userId): Collection
    {
        return $this->categoryRepository->getAvailableExpenseCategoriesForUser($userId);
    }

    public function createBudget(int $userId, array $data): Budget
    {
        $categoryExists = $this->categoryRepository
            ->existsAvailableExpenseCategoryForUser($data['category_id'], $userId);

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
