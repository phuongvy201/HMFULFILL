<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'first_name' => 'Mai',
                'last_name' => 'Support',
                'email' => 'support@gmail.com',
                'phone' => '0987654321',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'google_id' => false,
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Customer',
                'last_name' => 'Test',
                'email' => 'customer@gmail.com',
                'phone' => '0987654321',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'google_id' => false,
                'role' => 'customer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Google',
                'last_name' => 'User',
                'email' => 'google@example.com',
                'phone' => '0123456788',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'google_id' => true,
                'role' => 'customer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Thêm 10 người dùng ngẫu nhiên
        for ($i = 1; $i <= 10; $i++) {
            $users[] = [
                'first_name' => 'User',
                'last_name' => $i,
                'email' => "user{$i}@gmail.com",
                'phone' => '0987654' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'google_id' => false,
                'role' => 'customer',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('users')->insert($users);
    }
}
