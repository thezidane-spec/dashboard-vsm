<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin VSM',
                'email' => 'admin@mudalaya.co.id',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ],
            [
                'name' => 'Tim PPIC',
                'email' => 'ppic@mudalaya.co.id',
                'password' => Hash::make('password123'),
                'role' => 'ppic',
            ],
            [
                'name' => 'Tim Engineering',
                'email' => 'engineering@mudalaya.co.id',
                'password' => Hash::make('password123'),
                'role' => 'engineering',
            ],
            [
                'name' => 'Tim Procurement',
                'email' => 'procurement@mudalaya.co.id',
                'password' => Hash::make('password123'),
                'role' => 'procurement',
            ],
            [
                'name' => 'Tim Production',
                'email' => 'production@mudalaya.co.id',
                'password' => Hash::make('password123'),
                'role' => 'production',
            ],
            [
                'name' => 'Tim QC QA',
                'email' => 'qc@mudalaya.co.id',
                'password' => Hash::make('password123'),
                'role' => 'qc_qa',
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(['email' => $user['email']], $user);
        }
    }
}