<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Создаём пользователей
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@teach.ai.atu.kz',
                'password' => Hash::make('password'),
                'roles' => ['admin'],
            ],
            [
                'name' => 'User User',
                'email' => 'user@teach.ai.atu.kz',
                'password' => Hash::make('password'),
                'roles' => ['user'],
            ],
        ];

        foreach ($users as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => $data['password'],
                ]
            );

            // Привязываем роли
            $roleIds = Role::whereIn('name', $data['roles'])->pluck('id');
            $user->roles()->sync($roleIds);
        }
    }
}
