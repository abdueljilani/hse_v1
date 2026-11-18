<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'Safety Officer',
            'email' => 'officer@example.com',
            'role' => 'hse_officer',
        ]);

        User::factory()->create([
            'name' => 'Employee',
            'email' => 'employee@example.com',
            'role' => 'employee',
        ]);
    }
}
