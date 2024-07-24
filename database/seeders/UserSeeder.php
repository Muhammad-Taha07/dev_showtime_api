<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'first_name'    => 'Admin',
                'last_name'     => 'User',
                'email'         => 'admin@gmail.com',
                'password'      => Hash::make('abc123$A'),
                'is_admin'      => 1,
                'status'        => 1,
                'is_verified'   => 1,
            ],
            [
                'first_name'    => 'Kevin',
                'last_name'     => 'Nash',
                'email'         => 'kevin@gmail.com',
                'password'      => Hash::make('abc123$A'),
                'is_admin'      => 0,
                'status'        => 1,
                'is_verified'   => 1,
            ],
            [
                'first_name'    => 'Glen',
                'last_name'     => 'Hasting',
                'email'         => 'glen@gmail.com',
                'password'      => Hash::make('abc123$A'),
                'is_admin'      => 0,
                'status'        => 1,
                'is_verified'   => 1,
            ],
            [
                'first_name'    => 'John',
                'last_name'     => 'Karmer',
                'email'         => 'karmer@gmail.com',
                'password'      => Hash::make('abc123$A'),
                'is_admin'      => 0,
                'status'        => 1,
                'is_verified'   => 1,
            ],
        ]);
    }
}
