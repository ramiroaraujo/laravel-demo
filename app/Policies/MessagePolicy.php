<?php

namespace App\Policies;

use App\Models\Message;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class MessagePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can update the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Message $message
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Message $message)
    {
        if ($message->user_id !== $user->id) return Response::deny();

        return $message->created_at->addMinutes(5)->greaterThan(\Carbon\Carbon::now()) ?
            Response::allow() : Response::deny('messages older than 5 minutes cannot be edited');
    }

    public function delete(User $user, Message $message)
    {
        return $message->user_id === $user->id;
    }

}
