<?php

namespace App\Policies;

use App\Models\Thread;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 *
 */
class ThreadPolicy
{
    use HandlesAuthorization;

    /**
     * Only Thread owner can update them
     *
     */
    public function update(User $user, Thread $thread)
    {
        return $thread->user_id === $user->id;
    }

    /**
     * Only Thread owner can delete them
     */
    public function delete(User $user, Thread $thread)
    {
        return $thread->user_id === $user->id;
    }
}
