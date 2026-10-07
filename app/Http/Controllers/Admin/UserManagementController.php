<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Interfaces\UserManagementServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function __construct(
        private readonly UserManagementServiceInterface $userManagementService,
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'status']);
        $users = $this->userManagementService->getUsersPaginated($filters);

        return view('admin.users.index', compact('users'));
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        $user = $this->userManagementService->toggleUserStatus($user);

        $statusText = $user->status === 'ACTIVE' ? 'kích hoạt' : 'vô hiệu hóa';

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Tài khoản {$user->name} đã được {$statusText}.");
    }
}
