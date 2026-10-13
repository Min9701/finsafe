<?php

namespace App\Http\Controllers;

use App\Services\Interfaces\DashboardServiceInterface;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardServiceInterface $dashboardService,
    ) {}

    public function __invoke(Request $request): View
    {
        $data = $this->dashboardService->getDashboardData($request->user()->id);

        return view('dashboard', $data);
    }
}
