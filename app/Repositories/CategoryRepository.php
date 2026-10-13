<?php

namespace App\Repositories;

use App\Models\Category;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function getForUser(?int $userId): Collection
    {
        return Category::where(function ($q) use ($userId) {
            $q->whereNull('user_id');
            if ($userId) {
                $q->orWhere('user_id', $userId);
            }
        })->orderBy('name')->get();
    }

    public function getForUserOrdered(?int $userId): Collection
    {
        return Category::where(function ($q) use ($userId) {
            $q->whereNull('user_id');
            if ($userId) {
                $q->orWhere('user_id', $userId);
            }
        })->orderBy('type')->orderBy('name')->get();
    }

    /**
     * Lấy danh mục chi tiêu khả dụng để lập ngân sách.
     *
     * @return Collection<int, Category>
     */
    public function getAvailableExpenseCategoriesForUser(int $userId): Collection
    {
        return $this->availableExpenseCategoriesForUser($userId)->orderBy('name')->get();
    }

    /**
     * Kiểm tra quyền sử dụng danh mục chi tiêu để lập ngân sách.
     */
    public function existsAvailableExpenseCategoryForUser(int $categoryId, int $userId): bool
    {
        return $this->availableExpenseCategoriesForUser($userId)->whereKey($categoryId)->exists();
    }

    /**
     * Tạo query chỉ gồm danh mục chi tiêu toàn cục hoặc của người dùng.
     *
     * @return Builder<Category>
     */
    private function availableExpenseCategoriesForUser(int $userId): Builder
    {
        return Category::query()->where('type', 'expense')->where(function ($query) use ($userId) {
            $query->whereNull('user_id')->orWhere('user_id', $userId);
        });
    }
}
