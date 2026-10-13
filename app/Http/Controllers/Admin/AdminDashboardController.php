<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Interfaces\UserManagementServiceInterface;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __construct(
        private readonly UserManagementServiceInterface $userManagementService,
    ) {}

    public function index(): View
    {
        $data = $this->userManagementService->getAdminDashboardData();

        return view('admin.dashboard', $data);
    }
}
