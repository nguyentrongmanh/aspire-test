<?php

namespace App\Providers;

use App\Interfaces\LoanRepositoryInterface;
use App\Interfaces\RepaymentRepositoryInterface;
use App\Repositories\LoanRepository;
use App\Repositories\RepaymentRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(LoanRepositoryInterface::class, LoanRepository::class);
        $this->app->bind(RepaymentRepositoryInterface::class, RepaymentRepository::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
