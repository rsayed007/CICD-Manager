<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if user exists to avoid duplicates
        if (!User::where('email', 'roman@innovatesolution.com')->exists()) {
            User::create([
                'name' => 'Roman',
                'email' => 'roman@innovatesolution.com',
                'password' => Hash::make('Roman@123'),
                'email_verified_at' => now(),
            ]);
            $this->command->info('User roman@innovatesolution.com created.');
        } else {
            $this->command->info('User roman@innovatesolution.com already exists.');
        }
    }
}
