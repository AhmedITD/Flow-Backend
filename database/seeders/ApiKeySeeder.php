<?php

namespace Database\Seeders;

use App\Models\ApiKey;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ApiKeySeeder extends Seeder
{
    public function run(): void
    {
        $adminUser = User::where('phone_number', '9647716418740')->first();
        $demoUser = User::where('phone_number', '9647501234567')->first();

        // Create a known API key for testing (admin)
        if ($adminUser) {
            $adminSubscription = Subscription::where('user_id', $adminUser->id)
                ->where('status', 'active')
                ->first();

            // Test API key with known value for development
            $testKey = 'flw_test_admin_key_12345678';
            ApiKey::updateOrCreate(
                ['user_id' => $adminUser->id, 'name' => 'Admin Test Key'],
                [
                    'subscription_id' => $adminSubscription?->id,
                    'key_hash' => hash('sha256', $testKey),
                    'key_prefix' => substr($testKey, 0, 12),
                    'scopes' => ['chat', 'tickets', 'admin'],
                    'status' => 'active',
                    'expires_at' => now()->addYear(),
                ]
            );

            // Production-like API key
            ApiKey::factory()
                ->for($adminUser)
                ->active()
                ->create([
                    'name' => 'Production API Key',
                    'subscription_id' => $adminSubscription?->id,
                ]);
        }

        // Create API key for demo user
        if ($demoUser) {
            $demoSubscription = Subscription::where('user_id', $demoUser->id)->first();

            $demoKey = 'flw_test_demo_key_87654321';
            ApiKey::updateOrCreate(
                ['user_id' => $demoUser->id, 'name' => 'Demo Test Key'],
                [
                    'subscription_id' => $demoSubscription?->id,
                    'key_hash' => hash('sha256', $demoKey),
                    'key_prefix' => substr($demoKey, 0, 12),
                    'scopes' => ['chat', 'tickets'],
                    'status' => 'active',
                    'expires_at' => now()->addMonths(3),
                ]
            );
        }

        // Create API keys for other users
        $otherUsers = User::whereNotIn('phone_number', ['9647716418740', '9647501234567'])->get();

        foreach ($otherUsers as $user) {
            $subscription = Subscription::where('user_id', $user->id)->first();
            
            ApiKey::factory()
                ->for($user)
                ->active()
                ->create([
                    'subscription_id' => $subscription?->id,
                ]);
        }

        $this->command->info('API Keys seeded successfully.');
        $this->command->info('Test API Keys for development:');
        $this->command->info('  Admin: flw_test_admin_key_12345678');
        $this->command->info('  Demo:  flw_test_demo_key_87654321');
    }
}

