<?php

namespace App\Policies;

use App\Models\Bookmark;
use App\Models\User;

class BookmarkPolicy
{
    public function delete(User $user, Bookmark $bookmark)
    {
        return $user->id === $bookmark->user_id;
    }
} 