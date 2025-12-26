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
        // Seed Balance module accounts first
        $this->call([
            \WuriN7i\Balance\Database\Seeders\AccountSeeder::class,
        ]);

        // Seed Bendahara divisions and mappings
        $this->call([
            DivisionSeeder::class,
            DivisionAccountSeeder::class,
        ]);

        // Create test user
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->command->info('✓ Database seeding completed successfully');
    }
}
