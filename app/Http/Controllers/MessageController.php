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
     * Store a newly created resource in storage.
     */
    public function store(CreateMessageRequest $request, Thread $thread)
    {
        return Message::create([...$request->validated(), 'thread_id' => $thread->id, 'user_id' => Auth::id()]);
    }

    /**
     * Update the specified resource in storage.
     * @throws AuthorizationException|\Throwable
     */
    public function update(Request $request, Thread $thread, Message $message)
    {
        $this->authorize('update', $message);

        $message->body = $request->body;
        $message->saveOrFail();
        return $message;
    }

    /**
     * Remove the specified resource from storage.
     * @throws AuthorizationException
     */
    public function destroy(Request $request, Message $message)
    {
        $this->authorize('delete', $message);

        $message->delete();
    }
}
