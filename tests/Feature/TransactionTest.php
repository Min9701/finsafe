<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Category $incomeCategory;

    private Category $expenseCategory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'role' => 'USER',
            'status' => 'ACTIVE',
        ]);

        $this->incomeCategory = new Category([
            'name' => 'Lương',
            'type' => 'income',
            'is_default' => true,
        ]);
        $this->incomeCategory->save();

        $this->expenseCategory = new Category([
            'name' => 'Ăn uống',
            'type' => 'expense',
            'is_default' => true,
        ]);
        $this->expenseCategory->save();
    }

    public function test_user_can_view_transaction_list(): void
    {
        $response = $this->actingAs($this->user)->get('/transactions');
        $response->assertStatus(200);
    }

    public function test_user_can_create_income_transaction(): void
    {
        $response = $this->actingAs($this->user)->post('/transactions', [
            'type' => 'income',
            'category_id' => $this->incomeCategory->id,
            'amount' => 5000000,
            'transaction_date' => now()->format('Y-m-d'),
            'description' => 'Lương tháng này',
        ]);

        $response->assertRedirect('/transactions');
        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'type' => 'income',
            'amount' => 5000000,
            'description' => 'Lương tháng này',
        ]);
    }

    public function test_user_can_create_expense_transaction(): void
    {
        $response = $this->actingAs($this->user)->post('/transactions', [
            'type' => 'expense',
            'category_id' => $this->expenseCategory->id,
            'amount' => 50000,
            'transaction_date' => now()->format('Y-m-d'),
            'description' => 'Ăn trưa',
        ]);

        $response->assertRedirect('/transactions');
        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'type' => 'expense',
            'amount' => 50000,
        ]);
    }

    public function test_user_can_update_own_transaction(): void
    {
        $transaction = Transaction::create([
            'user_id' => $this->user->id,
            'category_id' => $this->expenseCategory->id,
            'type' => 'expense',
            'amount' => 100000,
            'transaction_date' => now()->format('Y-m-d'),
            'description' => 'Cũ',
        ]);

        $response = $this->actingAs($this->user)->put("/transactions/{$transaction->id}", [
            'type' => 'expense',
            'category_id' => $this->expenseCategory->id,
            'amount' => 200000,
            'transaction_date' => now()->format('Y-m-d'),
            'description' => 'Mới',
        ]);

        $response->assertRedirect('/transactions');
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'amount' => 200000,
            'description' => 'Mới',
        ]);
    }

    public function test_user_cannot_update_other_users_transaction(): void
    {
        $otherUser = User::factory()->create(['role' => 'USER', 'status' => 'ACTIVE']);
        $transaction = Transaction::create([
            'user_id' => $otherUser->id,
            'category_id' => $this->expenseCategory->id,
            'type' => 'expense',
            'amount' => 100000,
            'transaction_date' => now()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($this->user)->put("/transactions/{$transaction->id}", [
            'type' => 'expense',
            'category_id' => $this->expenseCategory->id,
            'amount' => 200000,
            'transaction_date' => now()->format('Y-m-d'),
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_delete_own_transaction(): void
    {
        $transaction = Transaction::create([
            'user_id' => $this->user->id,
            'category_id' => $this->expenseCategory->id,
            'type' => 'expense',
            'amount' => 100000,
            'transaction_date' => now()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($this->user)->delete("/transactions/{$transaction->id}");
        $response->assertRedirect('/transactions');
        $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
    }

    public function test_guest_cannot_access_transactions(): void
    {
        $response = $this->get('/transactions');
        $response->assertRedirect('/login');
    }

    public function test_user_can_view_dashboard_with_category_expenses(): void
    {
        Transaction::create([
            'user_id' => $this->user->id,
            'category_id' => $this->expenseCategory->id,
            'type' => 'expense',
            'amount' => 150000,
            'transaction_date' => now(),
            'description' => 'Ăn trưa',
        ]);

        $response = $this->actingAs($this->user)->get('/');
        $response->assertStatus(200);
        $response->assertSee('Ăn uống');
    }
}
