<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'city' => 'Cotonou',
        ]);

        $this->call([
            AdminSeeder::class,
            CategorySeeder::class,
            ListingSeeder::class,
            ReviewSeeder::class,
            NotificationSeeder::class,
            ReportSeeder::class,
            MessageSeeder::class,
        ]);
    }
}
