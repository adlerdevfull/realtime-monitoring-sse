<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create(['name' => 'Admin', 'email' => 'admin@platform.test', 'password' => Hash::make('password')]);
        User::create(['name' => 'Monitor User', 'email' => 'monitor@platform.test', 'password' => Hash::make('password')]);
    }
}
