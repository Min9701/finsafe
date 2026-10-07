<?php

namespace App\Repositories;

use App\Models\Transaction;
use App\Repositories\Interfaces\TransactionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TransactionRepository implements TransactionRepositoryInterface
{
    public function getByUserPaginated(int $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Transaction::where('user_id', $userId)->with('category');

        if (! empty($filters['search'])) {
            $query->where('description', 'like', '%'.$filters['search'].'%');
        }

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (! empty($filters['date_from'])) {
            $query->where('transaction_date', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->where('transaction_date', '<=', $filters['date_to']);
        }

        if (! empty($filters['amount_min'])) {
            $query->where('amount', '>=', $filters['amount_min']);
        }

        if (! empty($filters['amount_max'])) {
            $query->where('amount', '<=', $filters['amount_max']);
        }

        return $query->orderByDesc('transaction_date')
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findOrFail(int $id): Transaction
    {
        return Transaction::findOrFail($id);
    }

    public function create(array $data): Transaction
    {
        return Transaction::create($data);
    }

    public function update(Transaction $transaction, array $data): bool
    {
        return $transaction->update($data);
    }

    public function delete(Transaction $transaction): bool
    {
        return $transaction->delete();
    }

    public function getSumByType(int $userId, string $type): float
    {
        return (float) Transaction::where('user_id', $userId)
            ->where('type', $type)
            ->sum('amount');
    }

    public function getMonthlyExpenses(int $userId, int $months = 6): Collection
    {
        $query = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->where('transaction_date', '>=', now()->subMonths($months - 1)->startOfMonth());

        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            $query->selectRaw("CAST(strftime('%Y', transaction_date) AS INTEGER) as year, CAST(strftime('%m', transaction_date) AS INTEGER) as month, SUM(amount) as total")
                ->groupByRaw("strftime('%Y', transaction_date), strftime('%m', transaction_date)")
                ->orderByRaw("strftime('%Y', transaction_date), strftime('%m', transaction_date)");
        } else {
            $query->selectRaw('YEAR(transaction_date) as year, MONTH(transaction_date) as month, SUM(amount) as total')
                ->groupByRaw('YEAR(transaction_date), MONTH(transaction_date)')
                ->orderByRaw('YEAR(transaction_date), MONTH(transaction_date)');
        }

        return $query->get();
    }

    public function getCategoryExpenses(int $userId, int $month, int $year): Collection
    {
        return Transaction::where('transactions.user_id', $userId)
            ->where('transactions.type', 'expense')
            ->whereMonth('transactions.transaction_date', $month)
            ->whereYear('transactions.transaction_date', $year)
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->selectRaw('categories.name as category_name, SUM(transactions.amount) as total')
            ->groupBy('categories.name')
            ->orderByDesc('total')
            ->get();
    }

    public function getRecentByUser(int $userId, int $limit = 10): Collection
    {
        return Transaction::where('user_id', $userId)
            ->with('category')
            ->orderByDesc('transaction_date')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
}
