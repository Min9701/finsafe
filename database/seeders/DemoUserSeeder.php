<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DemoUserSeeder extends Seeder
{
    /**
     * Create demo users and their income and expense transactions.
     */
    public function run(): void
    {
        $incomeCategories = Category::query()
            ->where('is_default', true)
            ->where('type', 'income')
            ->get();
        $expenseCategories = Category::query()
            ->where('is_default', true)
            ->where('type', 'expense')
            ->get();

        if ($incomeCategories->isEmpty() || $expenseCategories->isEmpty()) {
            $this->command?->error('Default income and expense categories are required.');

            return;
        }

        for ($userNumber = 1; $userNumber <= 20; $userNumber++) {
            $email = "user{$userNumber}@gmail.com";
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => "User {$userNumber}",
                    'password' => '12345678',
                    'role' => 'USER',
                    'status' => 'ACTIVE',
                    'email_verified_at' => now(),
                ]
            );

            UserProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'phone' => sprintf('09%09d', $userNumber),
                    'date_of_birth' => Carbon::today()->subYears(20 + $userNumber),
                    'avatar' => null,
                ]
            );

            Transaction::query()
                ->where('user_id', $user->id)
                ->where('description', 'like', 'Demo transaction %')
                ->delete();

            for ($transactionNumber = 1; $transactionNumber <= 10; $transactionNumber++) {
                $isIncome = $transactionNumber <= 5;
                $type = $isIncome ? 'income' : 'expense';
                $categories = $isIncome ? $incomeCategories : $expenseCategories;
                $category = $categories[($transactionNumber - 1) % $categories->count()];

                Transaction::create([
                    'user_id' => $user->id,
                    'category_id' => $category->id,
                    'type' => $type,
                    'amount' => $isIncome
                        ? 5000000 + ($transactionNumber * 250000)
                        : 100000 + ($transactionNumber * 50000),
                    'transaction_date' => Carbon::today()->subDays($transactionNumber),
                    'description' => "Demo transaction {$transactionNumber} ({$type})",
                ]);
            }
        }
    }
}
