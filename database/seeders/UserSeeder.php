<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Manager',
            'email' => 'manager@example.com',
            'password' => Hash::make('password'),
            'role' => 'manager',
        ]);

        User::create([
            'name' => 'User One',
            'email' => 'user1@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        User::create([
            'name' => 'User Two',
            'email' => 'user2@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);
    }
}
