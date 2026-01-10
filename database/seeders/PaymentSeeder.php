<?php

namespace Database\Seeders;

use App\Models\BillingCycle;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $activeSubscriptions = Subscription::where('status', 'active')->get();

        foreach ($activeSubscriptions as $subscription) {
            // Create completed payment for current subscription
            $payment = Payment::factory()
                ->completed()
                ->create([
                    'user_id' => $subscription->user_id,
                    'subscription_id' => $subscription->id,
                    'amount' => $subscription->plan->price ?? 29900,
                    'description' => "Subscription payment for {$subscription->plan->name}",
                ]);

            // Create billing cycle
            BillingCycle::factory()
                ->paid()
                ->currentMonth()
                ->create([
                    'subscription_id' => $subscription->id,
                    'payment_id' => $payment->id,
                    'amount' => $payment->amount,
                ]);

            // Create some historical payments (50% chance)
            if (rand(0, 1)) {
                $historicalPayment = Payment::factory()
                    ->completed()
                    ->create([
                        'user_id' => $subscription->user_id,
                        'subscription_id' => $subscription->id,
                        'amount' => $subscription->plan->price ?? 29900,
                        'created_at' => now()->subMonth(),
                    ]);

                BillingCycle::factory()
                    ->paid()
                    ->create([
                        'subscription_id' => $subscription->id,
                        'payment_id' => $historicalPayment->id,
                        'amount' => $historicalPayment->amount,
                        'period_start' => now()->subMonth()->startOfMonth(),
                        'period_end' => now()->subMonth()->endOfMonth(),
                        'paid_at' => now()->subMonth(),
                    ]);
            }
        }

        // Create some pending/failed payments for demo
        Payment::factory(3)->pending()->create();
        Payment::factory(2)->failed()->create();

        $this->command->info('Payments and billing cycles seeded successfully.');
    }
}

