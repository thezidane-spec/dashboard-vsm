<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'     => 'Admin Mei',
                'email'    => 'admin@mudalaya.co.id',
                'password' => Hash::make('password'),
                'role'     => 'admin',
            ],
            [
                'name'     => 'Tim PPIC',
                'email'    => 'ppic@mudalaya.co.id',
                'password' => Hash::make('password'),
                'role'     => 'ppic',
            ],
            [
                'name'     => 'Tim Engineering',
                'email'    => 'engineering@mudalaya.co.id',
                'password' => Hash::make('password'),
                'role'     => 'engineering',
            ],
            [
                'name'     => 'Tim Procurement',
                'email'    => 'procurement@mudalaya.co.id',
                'password' => Hash::make('password'),
                'role'     => 'procurement',
            ],
            [
                'name'     => 'Tim Production',
                'email'    => 'production@mudalaya.co.id',
                'password' => Hash::make('password'),
                'role'     => 'production',
            ],
            [
                'name'     => 'Tim QC QA',
                'email'    => 'qc_qa@mudalaya.co.id',
                'password' => Hash::make('password'),
                'role'     => 'qc_qa',
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}
