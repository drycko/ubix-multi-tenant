<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $tiers = [
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => 'Perfect for small properties',
                'monthly_price' => 29.99,
                'yearly_price' => 299.99,
                'max_properties' => 1,
                'max_users' => 2,
                'features' => ['basic_reporting', 'email_support'],
                'is_default' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Professional',
                'slug' => 'professional',
                'description' => 'For growing properties',
                'monthly_price' => 79.99,
                'yearly_price' => 799.99,
                'max_properties' => 3,
                'max_users' => 5,
                'features' => ['advanced_reporting', 'priority_support', 'multi_property'],
                'is_default' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'For large hotel chains',
                'monthly_price' => 199.99,
                'yearly_price' => 1999.99,
                'max_properties' => 10,
                'max_users' => 20,
                'features' => ['full_analytics', '24_7_support', 'api_access'],
                'is_default' => false,
                'is_active' => true,
            ],
        ];

        foreach ($tiers as $tier) {
            SubscriptionPlan::create($tier);
        }

        $this->command->info('Subscription plans seeded successfully.');
    }
}