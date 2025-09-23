<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CentralSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;


class CentralSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'site_name', 'value' => 'Ubix Multi-Tenant'],
            ['key' => 'site_logo', 'value' => null],
            ['key' => 'support_email', 'value' => 'support@nexusflow.com'],
            ['key' => 'default_language', 'value' => 'en'],
            ['key' => 'timezone', 'value' => 'UTC'],
            ['key' => 'currency', 'value' => 'USD'],
            ['key' => 'items_per_page', 'value' => '10'],
            ['key' => 'maintenance_mode', 'value' => '0'],
            ['key' => 'terms_of_service', 'value' => null],
            ['key' => 'privacy_policy', 'value' => null],
            ['key' => 'google_analytics_id', 'value' => null],
            ['key' => 'facebook_pixel_id', 'value' => null],
            ['key' => 'smtp_host', 'value' => null],
            ['key' => 'smtp_port', 'value' => null],
            ['key' => 'smtp_username', 'value' => null],
            ['key' => 'smtp_password', 'value' => null],
            ['key' => 'smtp_encryption', 'value' => null],
            ['key' => 'default_subscription_plan', 'value' => null],
            ['key' => 'trial_period_days', 'value' => '14'],
        ];

        foreach ($settings as $setting) {
            CentralSetting::updateOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value']]
            );
        }
    }
}
