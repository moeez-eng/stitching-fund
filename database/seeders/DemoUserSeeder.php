<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Services\Demo\DemoSeederService;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'demo@stitchingfund.test'],
            [
                'name' => 'Demo User',
                'password' => Hash::make('password'),
                'is_demo' => true,
                'demo_expires_at' => now()->addDays(7),
            ]
        );

        // Assign role if Spatie is used
        if (method_exists($user, 'assignRole')) {
            $user->assignRole('Demo User');
        }

        (new DemoSeederService())->seedForUser($user);
    }
}
