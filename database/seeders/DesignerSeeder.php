<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DesignerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $designers = [
            [
                'first_name' => 'Nguyễn',
                'last_name' => 'Thiết Kế',
                'email' => 'designer1@gmail.com',
                'phone' => '0987654321',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'google_id' => false,
                'role' => 'design',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Trần',
                'last_name' => 'Designer',
                'email' => 'designer2@gmail.com',
                'phone' => '0987654322',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'google_id' => false,
                'role' => 'design',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('users')->insert($designers);
    }
}
