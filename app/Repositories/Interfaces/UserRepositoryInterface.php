<?php

namespace App\Repositories\Interfaces;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface UserRepositoryInterface
{
    public function getPaginatedUsers(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): User;

    public function countByRole(string $role): int;

    public function countByRoleAndStatus(string $role, string $status): int;

    public function countNewUsersThisMonth(string $role): int;

    public function getMonthlyRegistrations(string $role, int $months = 6): Collection;

    public function toggleStatus(User $user): User;
}
