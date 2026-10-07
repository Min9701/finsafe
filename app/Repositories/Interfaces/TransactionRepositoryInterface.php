<?php

namespace App\Repositories\Interfaces;

use App\Models\Transaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface TransactionRepositoryInterface
{
    public function getByUserPaginated(int $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): Transaction;

    public function create(array $data): Transaction;

    public function update(Transaction $transaction, array $data): bool;

    public function delete(Transaction $transaction): bool;

    public function getSumByType(int $userId, string $type): float;

    public function getMonthlyExpenses(int $userId, int $months = 6): Collection;

    public function getCategoryExpenses(int $userId, int $month, int $year): Collection;

    public function getRecentByUser(int $userId, int $limit = 10): Collection;
}
