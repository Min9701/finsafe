<?php

namespace App\Services\Interfaces;

use App\Models\Transaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface TransactionServiceInterface
{
    public function getTransactionsPaginated(int $userId, array $filters = []): LengthAwarePaginator;

    public function getCategoriesForUser(int $userId): Collection;

    public function getCategoriesForUserOrdered(int $userId): Collection;

    public function createTransaction(int $userId, array $data): Transaction;

    public function updateTransaction(Transaction $transaction, array $data): bool;

    public function deleteTransaction(Transaction $transaction): bool;

    public function authorizeTransaction(int $userId, Transaction $transaction): void;
}
