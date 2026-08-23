<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@kaba.bj'],
            [
                'name'              => 'Admin KABA',
                'password'          => Hash::make('password'),
                'role'              => 'admin',
                'is_verified'       => true,
                'city'              => 'Cotonou',
                'email_verified_at' => now(),
            ]
        );
    }
}
