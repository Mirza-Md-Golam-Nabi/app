<?php

namespace App\Traits;

use App\Enums\UserStatus;
use App\Enums\UserType;

trait HasUserType
{
    public function isSuperAdmin(): bool
    {
        return $this->user_type === UserType::SuperAdmin;
    }

    public function isAdmin(): bool
    {
        return $this->user_type === UserType::Admin;
    }

    public function isUser(): bool
    {
        return $this->user_type === UserType::User;
    }

    public function isActive(): bool
    {
        return $this->status === UserStatus::Active;
    }
}
