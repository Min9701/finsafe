<?php

namespace App\Services;

use App\Models\Transaction;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\TransactionRepositoryInterface;
use App\Services\Interfaces\TransactionServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class TransactionService implements TransactionServiceInterface
{
    public function __construct(
        private readonly TransactionRepositoryInterface $transactionRepository,
        private readonly CategoryRepositoryInterface $categoryRepository,
    ) {}

    public function getTransactionsPaginated(int $userId, array $filters = []): LengthAwarePaginator
    {
        return $this->transactionRepository->getByUserPaginated($userId, $filters);
    }

    public function getCategoriesForUser(int $userId): Collection
    {
        return $this->categoryRepository->getForUser($userId);
    }

    public function getCategoriesForUserOrdered(int $userId): Collection
    {
        return $this->categoryRepository->getForUserOrdered($userId);
    }

    public function createTransaction(int $userId, array $data): Transaction
    {
        $data['user_id'] = $userId;

        return $this->transactionRepository->create($data);
    }

    public function updateTransaction(Transaction $transaction, array $data): bool
    {
        return $this->transactionRepository->update($transaction, $data);
    }

    public function deleteTransaction(Transaction $transaction): bool
    {
        return $this->transactionRepository->delete($transaction);
    }

    public function authorizeTransaction(int $userId, Transaction $transaction): void
    {
        if ($transaction->user_id !== $userId) {
            throw new AccessDeniedHttpException;
        }
    }
}
