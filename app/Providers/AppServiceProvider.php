<?php

namespace App\Providers;

use App\Repositories\BudgetRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\Interfaces\BudgetRepositoryInterface;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\TransactionRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\TransactionRepository;
use App\Repositories\UserRepository;
use App\Services\BudgetService;
use App\Services\DashboardService;
use App\Services\Interfaces\BudgetServiceInterface;
use App\Services\Interfaces\DashboardServiceInterface;
use App\Services\Interfaces\TransactionServiceInterface;
use App\Services\Interfaces\UserManagementServiceInterface;
use App\Services\TransactionService;
use App\Services\UserManagementService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * All of the container singletons that should be registered.
     *
     * @var array<class-string, class-string>
     */
    public array $singletons = [
        // Repositories
        TransactionRepositoryInterface::class => TransactionRepository::class,
        BudgetRepositoryInterface::class => BudgetRepository::class,
        CategoryRepositoryInterface::class => CategoryRepository::class,
        UserRepositoryInterface::class => UserRepository::class,

        // Services
        TransactionServiceInterface::class => TransactionService::class,
        BudgetServiceInterface::class => BudgetService::class,
        DashboardServiceInterface::class => DashboardService::class,
        UserManagementServiceInterface::class => UserManagementService::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
