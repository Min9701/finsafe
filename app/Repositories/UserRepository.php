<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class UserRepository implements UserRepositoryInterface
{
    public function getPaginatedUsers(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = User::where('role', 'USER');

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findOrFail(int $id): User
    {
        return User::findOrFail($id);
    }

    public function countByRole(string $role): int
    {
        return User::where('role', $role)->count();
    }

    public function countByRoleAndStatus(string $role, string $status): int
    {
        return User::where('role', $role)->where('status', $status)->count();
    }

    public function countNewUsersThisMonth(string $role): int
    {
        return User::where('role', $role)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
    }

    public function getMonthlyRegistrations(string $role, int $months = 6): Collection
    {
        $query = User::where('role', $role)
            ->where('created_at', '>=', now()->subMonths($months - 1)->startOfMonth());

        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            $query->selectRaw("CAST(strftime('%Y', created_at) AS INTEGER) as year, CAST(strftime('%m', created_at) AS INTEGER) as month, COUNT(*) as total")
                ->groupByRaw("strftime('%Y', created_at), strftime('%m', created_at)")
                ->orderByRaw("strftime('%Y', created_at), strftime('%m', created_at)");
        } else {
            $query->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as total')
                ->groupByRaw('YEAR(created_at), MONTH(created_at)')
                ->orderByRaw('YEAR(created_at), MONTH(created_at)');
        }

        return $query->get();
    }

    public function toggleStatus(User $user): User
    {
        $user->status = $user->status === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE';
        $user->save();

        return $user;
    }
}
