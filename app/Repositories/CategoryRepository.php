<?php

namespace App\Repositories;

use App\Models\Category;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
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
}
