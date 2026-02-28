<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'data' => [
                    'name' => 'admin',
                    'email' => 'admin@mail.com',
                    'password' => bcrypt('password'),
                ],
                'roles' => 'admin',
            ],
            [
                'data' => [
                    'name' => 'user',
                    'email' => 'user@mail.com',
                    'password' => bcrypt('password'),
                ],
            ],
        ];

        foreach ($users as $user) {
            try {
                $createdUser = User::create($user['data']);
                if (isset($user['roles'])) {
                    $createdUser->assignRole($user['roles']);
                }
            } catch (\Exception $e) {
                Log::info($e->getMessage());
            }
        }
    }
}
