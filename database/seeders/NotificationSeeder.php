<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        // Get all users to ensure notifications are created for existing users
        $users = User::all();
        
        if ($users->isEmpty()) {
            // Create a test user if no users exist
            $user = User::create([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => bcrypt('password'),
                'role' => 'Agency Owner',
                'status' => 'active',
                'email_verified_at' => now(),
            ]);
            $users = collect([$user]);
        }

        // Create notifications for each user
        foreach ($users as $user) {
            $notifications = [
                [
                    'id' => Str::uuid(),
                    'type' => 'App\\Notifications\\TestNotification',
                    'notifiable_type' => User::class,
                    'notifiable_id' => $user->id,
                    'data' => json_encode([
                        'title' => 'Welcome to Lotrix!',
                        'message' => 'Your account has been successfully created.',
                        'action_url' => null
                    ]),
                    'read_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => Str::uuid(),
                    'type' => 'App\\Notifications\\TestNotification',
                    'notifiable_type' => User::class,
                    'notifiable_id' => $user->id,
                    'data' => json_encode([
                        'title' => 'New Investment Opportunity',
                        'message' => 'A new investment pool is available for review.',
                        'action_url' => '/admin/investments'
                    ]),
                    'read_at' => null,
                    'created_at' => now()->subMinutes(30),
                    'updated_at' => now()->subMinutes(30),
                ],
                [
                    'id' => Str::uuid(),
                    'type' => 'App\\Notifications\\TestNotification',
                    'notifiable_type' => User::class,
                    'notifiable_id' => $user->id,
                    'data' => json_encode([
                        'title' => 'Monthly Report Available',
                        'message' => 'Your monthly investment report is ready to view.',
                        'action_url' => '/admin/reports'
                    ]),
                    'read_at' => now()->subHour(), // This one is read
                    'created_at' => now()->subHours(2),
                    'updated_at' => now()->subHour(),
                ],
            ];

            DB::table('notifications')->insert($notifications);
        }
    }
}
