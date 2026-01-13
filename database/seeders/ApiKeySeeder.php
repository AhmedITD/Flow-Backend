<?php

namespace Database\Seeders;

use App\Models\ApiKey;
use App\Models\ApiKeyService;
use App\Models\ServiceAccount;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ApiKeySeeder extends Seeder
{
    /**
     * Seed test API keys.
     */
    public function run(): void
    {
        $this->command->info('Seeding API keys...');

        // Get test users (phone numbers stored without + prefix)
        $adminUser = User::where('phone_number', '9647716418740')->first();
        $demoUser = User::where('phone_number', '9647501234567')->first();

        if (!$adminUser || !$demoUser) {
            $this->command->warn('  Test users not found. Skipping API key seeding.');
            return;
        }

        // Get their service accounts
        $adminAccount = ServiceAccount::where('user_id', $adminUser->id)->first();
        $demoAccount = ServiceAccount::where('user_id', $demoUser->id)->first();

        // Admin API Key - Full access
        $adminKey = $this->createApiKey(
            $adminUser,
            $adminAccount,
            'Admin Test Key',
            'flw_test_admin_key_12345678',
            ['call_center', 'hr']
        );
        $this->command->info("  Created Admin API key: flw_test_admin_key_12345678");

        // Demo API Key - Call Center only
        $demoKey = $this->createApiKey(
            $demoUser,
            $demoAccount,
            'Demo Test Key',
            'flw_test_demo_key_87654321',
            ['call_center']
        );
        $this->command->info("  Created Demo API key: flw_test_demo_key_87654321");
    }

    private function createApiKey(
        User $user,
        ?ServiceAccount $serviceAccount,
        string $name,
        string $plainKey,
        array $services
    ): ApiKey {
        $apiKey = ApiKey::create([
            'user_id' => $user->id,
            'service_account_id' => $serviceAccount?->id,
            'name' => $name,
            'key_hash' => hash('sha256', $plainKey),
            'key_prefix' => substr($plainKey, 0, 20),
            'status' => 'active',
            'expires_at' => null, // Never expires
            'metadata' => [
                'created_by' => 'seeder',
                'environment' => 'test',
            ],
        ]);

        // Attach services
        foreach ($services as $service) {
            ApiKeyService::create([
                'api_key_id' => $apiKey->id,
                'service_type' => $service,
            ]);
        }

        return $apiKey;
    }
}
