<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class EnsureLocalAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ensure:local-admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates local admin users for offline login support';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Ensuring local admin users exist for offline access...");

        // 1. Super Admin
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'admin',
                'password' => Hash::make('admin'),
            ]
        );
        $this->info("✅ User 'admin' (admin@example.com) ready.");

        // 2. Sneaky Admin
        $sneaky = User::updateOrCreate(
            ['email' => 'sneaky@example.com'],
            [
                'name' => 'sneaky',
                'password' => Hash::make('sneaky'),
            ]
        );
        $this->info("✅ User 'sneaky' (sneaky@example.com) ready.");

        $this->info("\nDone! You can now log in even if the Django API is offline.");
    }
}
