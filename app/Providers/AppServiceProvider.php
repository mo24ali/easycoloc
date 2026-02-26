<?php

namespace App\Providers;

use App\Models\Collocation;
use App\Models\Expense;
use App\Policies\CollocationPolicy;
use App\Policies\ExpensePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        Gate::policy(Collocation::class, CollocationPolicy::class);
        Gate::policy(Expense::class, ExpensePolicy::class);
    }
}
