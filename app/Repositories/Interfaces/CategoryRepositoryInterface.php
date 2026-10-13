<?php

namespace App\Repositories\Interfaces;

use App\Models\Category;
use Illuminate\Support\Collection;

interface CategoryRepositoryInterface
{
    /**
     * Lấy danh mục toàn cục và danh mục riêng có thể dùng bởi người dùng.
     *
     * @param  int|null  $userId  ID người dùng; null chỉ lấy danh mục toàn cục.
     * @return Collection<int, Category>
     */
    public function getForUser(?int $userId): Collection;

    /**
     * Lấy danh mục khả dụng theo loại rồi tên.
     *
     * @param  int|null  $userId  ID người dùng; null chỉ lấy danh mục toàn cục.
     * @return Collection<int, Category>
     */
    public function getForUserOrdered(?int $userId): Collection;

    /**
     * Lấy danh mục chi tiêu toàn cục hoặc thuộc người dùng để lập ngân sách.
     *
     * @param  int  $userId  ID người dùng.
     * @return Collection<int, Category>
     */
    public function getAvailableExpenseCategoriesForUser(int $userId): Collection;

    /**
     * Kiểm tra danh mục chi tiêu có khả dụng cho người dùng hay không.
     *
     * @param  int  $categoryId  ID danh mục.
     * @param  int  $userId  ID người dùng.
     */
    public function existsAvailableExpenseCategoryForUser(int $categoryId, int $userId): bool;
}
