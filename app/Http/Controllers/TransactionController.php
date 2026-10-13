<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransactionRequest;
use App\Models\Transaction;
use App\Services\Interfaces\TransactionServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function __construct(
        private readonly TransactionServiceInterface $transactionService,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        $filters = $request->only([
            'search', 'type', 'category_id',
            'date_from', 'date_to',
            'amount_min', 'amount_max',
        ]);

        $transactions = $this->transactionService->getTransactionsPaginated($user->id, $filters);
        $categories = $this->transactionService->getCategoriesForUser($user->id);

        return view('transactions.index', compact('transactions', 'categories'));
    }

    public function create(Request $request): View
    {
        $categories = $this->transactionService->getCategoriesForUserOrdered($request->user()->id);

        return view('transactions.create', compact('categories'));
    }

    public function store(TransactionRequest $request): RedirectResponse
    {
        $this->transactionService->createTransaction(
            $request->user()->id,
            $request->validated(),
        );

        return redirect()
            ->route('transactions.index')
            ->with('success', 'Giao dịch đã được tạo thành công.');
    }

    public function edit(Request $request, Transaction $transaction): View
    {
        $this->transactionService->authorizeTransaction($request->user()->id, $transaction);

        $categories = $this->transactionService->getCategoriesForUserOrdered($request->user()->id);

        return view('transactions.edit', compact('transaction', 'categories'));
    }

    public function update(TransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        $this->transactionService->authorizeTransaction($request->user()->id, $transaction);

        $this->transactionService->updateTransaction($transaction, $request->validated());

        return redirect()
            ->route('transactions.index')
            ->with('success', 'Giao dịch đã được cập nhật thành công.');
    }

    public function destroy(Request $request, Transaction $transaction): RedirectResponse
    {
        $this->transactionService->authorizeTransaction($request->user()->id, $transaction);

        $this->transactionService->deleteTransaction($transaction);

        return redirect()
            ->route('transactions.index')
            ->with('success', 'Giao dịch đã được xóa thành công.');
    }
}
