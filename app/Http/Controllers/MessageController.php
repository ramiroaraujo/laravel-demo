<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateMessageRequest;
use App\Models\Message;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 *
 */
class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($thread_id)
    {
        return Message::whereThreadId($thread_id)->paginate(10);
    }

    /**
     * Perform a paginated search with scout, add the user data for each message
     */
    public function search(User $user)
    {
        $search = request('search');
        return Message::search($search)
            ->where('user_id', $user->id)
            ->query(function (Builder $builder) {
                $builder->with(['user' => fn(Relation $q) => $q->select(['id', 'name'])]);
            })
            ->paginate(10);
    }

    /**
     * Store the new Message linked to the logged in user
     */
    public function store(CreateMessageRequest $request, Thread $thread)
    {
        return Message::create([...$request->validated(), 'thread_id' => $thread->id, 'user_id' => Auth::id()]);
    }

    /**
     * Update the message text
     * @throws AuthorizationException|\Throwable
     */
    public function update(Request $request, Thread $thread, Message $message)
    {
        //only the creator can update it, and only if less than 5 minutes have passed since creation
        $this->authorize('update', $message);

        $message->body = $request->body;
        $message->saveOrFail();
        return $message;
    }

    /**
     * Delete the message
     * @throws AuthorizationException
     */
    public function destroy(Request $request, Thread $thread, Message $message)
    {
        //only the creator can delete it
        $this->authorize('delete', $message);

        $message->delete();
    }
}
