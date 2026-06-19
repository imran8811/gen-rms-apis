<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AuthUsersSeeder extends Seeder
{
    /**
     * Seed the portal's login accounts.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@genzfoods.pk'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('admin1234'),
                'role' => 'admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'user@genzfoods.pk'],
            [
                'name' => 'Cashier',
                'password' => Hash::make('user1234'),
                'role' => 'user',
            ]
        );
    }
}
