<?php

namespace App\Services;

use App\Repositories\Interfaces\TransactionRepositoryInterface;
use App\Services\Interfaces\DashboardServiceInterface;

class DashboardService implements DashboardServiceInterface
{
    public function __construct(
        private readonly TransactionRepositoryInterface $transactionRepository,
    ) {}

    public function getDashboardData(int $userId): array
    {
        $totalIncome = $this->transactionRepository->getSumByType($userId, 'income');
        $totalExpense = $this->transactionRepository->getSumByType($userId, 'expense');

        return [
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'balance' => $totalIncome - $totalExpense,
            'monthlyExpenses' => $this->transactionRepository->getMonthlyExpenses($userId),
            'categoryExpenses' => $this->transactionRepository->getCategoryExpenses(
                $userId,
                now()->month,
                now()->year,
            ),
            'recentTransactions' => $this->transactionRepository->getRecentByUser($userId),
        ];
    }
}
