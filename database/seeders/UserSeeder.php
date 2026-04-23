<?php

namespace Database\Seeders;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = $this->users();
        foreach ($users as $user) {
            User::factory()
                ->create($user);
        }
    }

    protected function users(): array
    {
        return [
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@example.com',
                'user_type' => UserType::SuperAdmin,
            ],
            [
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'user_type' => UserType::Admin,
            ],
            [
                'name' => 'User 1',
                'email' => 'user1@example.com',
                'user_type' => UserType::User,
            ],
            [
                'name' => 'User 2',
                'email' => 'user2@example.com',
                'user_type' => UserType::User,
            ],
        ];
    }
}
