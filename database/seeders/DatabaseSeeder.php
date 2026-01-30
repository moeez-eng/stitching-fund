<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application database.
     */
    public function run(): void
    {
        // Other seeders...
        $this->call([
            \Database\Seeders\RoleSeeder::class,
            \Database\Seeders\UserSeeder::class,
          
        ]);
    }
} 