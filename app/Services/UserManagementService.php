<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\Interfaces\UserManagementServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class UserManagementService implements UserManagementServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    public function getAdminDashboardData(): array
    {
        return [
            'totalUsers' => $this->userRepository->countByRole('USER'),
            'activeUsers' => $this->userRepository->countByRoleAndStatus('USER', 'ACTIVE'),
            'inactiveUsers' => $this->userRepository->countByRoleAndStatus('USER', 'INACTIVE'),
            'newUsersThisMonth' => $this->userRepository->countNewUsersThisMonth('USER'),
            'monthlyRegistrations' => $this->userRepository->getMonthlyRegistrations('USER'),
        ];
    }

    public function getUsersPaginated(array $filters = []): LengthAwarePaginator
    {
        return $this->userRepository->getPaginatedUsers($filters);
    }

    public function toggleUserStatus(User $user): User
    {
        if ($user->role === 'ADMIN') {
            throw new AccessDeniedHttpException;
        }

        return $this->userRepository->toggleStatus($user);
    }
}
