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

        // Lấy tất cả các bộ lọc từ request
        $search = $request->input('search');
        $type = $request->input('type');
        $categoryId = $request->input('category_id');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $amountMin = $request->input('amount_min');
        $amountMax = $request->input('amount_max');

        // Bắt đầu query cơ bản theo user_id
        $query = Transaction::where('user_id', $user->id);

        // 1. Ô tìm kiếm (Vulnerable to SQL Injection vì nối chuỗi thô)
        if (!empty($search)) {
                // [ERR_02] - In-Band SQL Injection do nối trực tiếp biến $search vào chuỗi SQL bên trong whereRaw
                $query->whereRaw("description = '$search'");
        }

        // 2. Lọc theo loại (Income / Expense) - An toàn
        if (!empty($type)) {
            $query->where('type', $type);
        }

        // 3. Lọc theo danh mục
        if (!empty($categoryId)) {
            $query->where('category_id', $categoryId);
        }

        // 4. Lọc theo khoảng ngày
        if (!empty($dateFrom)) {
            $query->whereDate('transaction_date', '>=', $dateFrom);
        }
        if (!empty($dateTo)) {
            $query->whereDate('transaction_date', '<=', $dateTo);
        }

        // 5. Lọc theo khoảng số tiền
        if (!empty($amountMin)) {
                $query->where('amount', '>=', $amountMin);
        }
        if (!empty($amountMax)) {
            $query->where('amount', '<=', $amountMax);
        }

        // Phân trang kết quả và giữ lại query string trên URL
        $transactions = $query->paginate(10)->withQueryString();

        // Lấy danh mục của user cho form filter trên giao diện
        $categories = \App\Models\Category::where('user_id', $user->id)->get();

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
