<?php

namespace App\Policies;

use App\Models\CustomJuz;
use App\Models\User;

class CustomJuzPolicy
{
    public function view(User $user, CustomJuz $customJuz)
    {
        return $user->id === $customJuz->user_id;
    }

    public function update(User $user, CustomJuz $customJuz)
    {
        return $user->id === $customJuz->user_id;
    }

    public function delete(User $user, CustomJuz $customJuz)
    {
        return $user->id === $customJuz->user_id;
    }
} 