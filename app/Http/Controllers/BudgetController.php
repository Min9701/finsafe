<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBudgetRequest;
use App\Services\Interfaces\BudgetServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BudgetController extends Controller
{
    public function __construct(
        private readonly BudgetServiceInterface $budgetService,
    ) {}

    public function index(Request $request): View
    {
        $userId = $request->user()->id;

        return view('budgets.index', [
            'budgets' => $this->budgetService->getBudgetsForCurrentMonth($userId),
            'categories' => $this->budgetService->getAvailableExpenseCategories($userId),
            'currentMonth' => now(),
        ]);
    }

    public function store(StoreBudgetRequest $request): RedirectResponse
    {
        $this->budgetService->createBudget($request->user()->id, $request->validated());

        return redirect()->route('budgets.index')->with('success', 'Đã tạo ngân sách cho tháng hiện tại.');
    }
}
