<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_budget_page_and_create_budget_for_current_month(): void
    {
        $user = User::factory()->create(['role' => 'USER', 'email_verified_at' => now()]);
        $category = Category::create([
            'name' => 'Ăn uống',
            'user_id' => $user->id,
            'type' => 'expense',
            'is_default' => false,
        ]);

        $this->actingAs($user)
            ->get(route('budgets.index'))
            ->assertStatus(200)
            ->assertSee('Tạo ngân sách tháng');

        $this->actingAs($user)
            ->post(route('budgets.store'), [
                'category_id' => $category->id,
                'amount' => 500000,
            ])
            ->assertRedirect(route('budgets.index'));

        $this->assertDatabaseHas('budgets', [
            'user_id' => $user->id,
            'category_id' => $category->id,
            'month' => now()->month,
            'year' => now()->year,
            'amount' => 500000,
        ]);
    }

    public function test_user_cannot_create_duplicate_budget_in_same_month(): void
    {
        $user = User::factory()->create(['role' => 'USER', 'email_verified_at' => now()]);
        $category = Category::create([
            'name' => 'Di chuyển',
            'user_id' => $user->id,
            'type' => 'expense',
            'is_default' => false,
        ]);

        $this->actingAs($user)->post(route('budgets.store'), [
            'category_id' => $category->id,
            'amount' => 500000,
        ]);

        $this->actingAs($user)
            ->post(route('budgets.store'), [
                'category_id' => $category->id,
                'amount' => 700000,
            ])
            ->assertSessionHasErrors(['category_id']);
    }
}
