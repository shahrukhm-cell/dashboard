<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => 'For small service teams getting organized.',
                'monthly_price' => 29,
                'max_users' => 5,
                'max_jobs' => 100,
                'features' => ['Customers', 'Service jobs', 'Basic reports'],
                'is_active' => true,
            ],
            [
                'name' => 'Growth',
                'slug' => 'growth',
                'description' => 'For growing teams that need payments and expenses.',
                'monthly_price' => 79,
                'max_users' => 20,
                'max_jobs' => 500,
                'features' => ['Everything in Starter', 'Payments', 'Expenses', 'Team tracking'],
                'is_active' => true,
            ],
            [
                'name' => 'Scale',
                'slug' => 'scale',
                'description' => 'For larger operations with unlimited workflow volume.',
                'monthly_price' => 149,
                'max_users' => null,
                'max_jobs' => null,
                'features' => ['Everything in Growth', 'Unlimited jobs', 'Advanced reporting'],
                'is_active' => true,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}