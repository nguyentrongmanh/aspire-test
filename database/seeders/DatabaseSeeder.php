<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Enums\UserRole;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\User::factory(10)->create();

        //create admin account
        \App\Models\User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'role' => UserRole::ADMIN,
        ]);

        //create user account
        \App\Models\User::factory()->create([
            'name' => 'Test User',
            'email' => 'test_account@example.com',
            'role' => UserRole::USER,
        ]);
    }
}
