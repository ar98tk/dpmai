<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\User;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $business = Business::firstOrCreate(
            ['name' => 'DPM Solutions'],
            ['status' => 'active']
        );

        User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'business_id' => $business->id,
                'name' => 'Abdelrahman Tarek',
                'password' => '123123123',
                'role' => 'admin',
                'is_active' => true,
            ]
        );
    }
}
