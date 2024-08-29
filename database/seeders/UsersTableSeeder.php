<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Insert the first user
        DB::table('users')->insert([
            'first_name' => 'Admin',
            'last_name' => 'Account',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('asd123$A'),
            'is_verified' => 1,
            'is_admin' => 1,
            'status' => 1,
        ]);

        // Insert additional users
        $users = [
            [
                'first_name' => 'Home',
                'last_name' => 'Lander',
                'email' => 'user1@yopmail.com',
                'password' => Hash::make('asd123$A'),
                'is_verified' => 1,
                'is_admin' => 0,
                'status' => 1,
            ],
            [
                'first_name' => 'William',
                'last_name' => 'Butcher',
                'email' => 'user2@yopmail.com',
                'password' => Hash::make('asd123$A'),
                'is_verified' => 1,
                'is_admin' => 0,
                'status' => 1,
            ],
            [
                'first_name' => 'Samuel',
                'last_name' => 'Jackson',
                'email' => 'user3@yopmail.com',
                'password' => Hash::make('asd123$A'),
                'is_verified' => 1,
                'is_admin' => 0,
                'status' => 1,
            ],
        ];

        // Insert the additional users into the database
        DB::table('users')->insert($users);
    }
}
