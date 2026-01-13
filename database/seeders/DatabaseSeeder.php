<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * 
     * Run: php artisan db:seed
     * Or:  php artisan migrate:fresh --seed
     */
    public function run(): void
    {
        $this->command->info('Starting database seeding...');
        $this->command->newLine();

        // 1. Core data (no dependencies)
        $this->call([
            UserSeeder::class,
            PricingSeeder::class,
        ]);

        // 2. Service Accounts (depends on Users)
        $this->call([
            ServiceAccountSeeder::class,
        ]);

        // 3. API Keys (depends on Users, Service Accounts)
        $this->call([
            ApiKeySeeder::class,
        ]);

        // 4. Tickets (standalone, uses Users for assignment)
        $this->call([
            TicketSeeder::class,
        ]);

        // 5. Usage and Payments (depends on Service Accounts)
        $this->call([
            UsageRecordSeeder::class,
            PaymentSeeder::class,
        ]);

        $this->command->newLine();
        $this->command->info('Database seeding completed!');
        $this->command->newLine();
        $this->command->info('Test accounts:');
        $this->command->info('  Phone: +9647716418740 | Password: password | Balance: 100,000 IQD');
        $this->command->info('  Phone: +9647501234567 | Password: password | Balance: 50,000 IQD');
        $this->command->newLine();
        $this->command->info('Test API Keys:');
        $this->command->info('  Admin: flw_test_admin_key_12345678 (call_center, hr)');
        $this->command->info('  Demo:  flw_test_demo_key_87654321 (call_center)');
        $this->command->newLine();
        $this->command->info('Pay-as-you-go Pricing:');
        $this->command->info('  Call Center: 50 IQD per 1000 tokens');
        $this->command->info('  HR: 75 IQD per 1000 tokens');
    }
}
