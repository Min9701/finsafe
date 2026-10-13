<?php

namespace App\Services\Interfaces;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserManagementServiceInterface
{
    public function getAdminDashboardData(): array;

    public function getUsersPaginated(array $filters = []): LengthAwarePaginator;

    public function toggleUserStatus(User $user): User;
}
