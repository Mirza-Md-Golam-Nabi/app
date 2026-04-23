<?php

namespace App\Filament\User\Pages\Auth;

use App\Enums\UserStatus;
use App\Enums\UserType;
use App\Models\User;
use Filament\Auth\Pages\Register as BaseRegister;

class Register extends BaseRegister
{
    protected function handleRegistration(array $data): User
    {
        $user = $this->getUserModel()::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'status' => UserStatus::Active,
            'user_type' => UserType::User,
        ]);

        return $user;
    }
}
