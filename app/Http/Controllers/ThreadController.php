<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateThreadRequest;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 *
 */
class ThreadController extends Controller
{
    /**
     * Display the Threads, paginated
     * if an optional user is passed, filter the list by the threads created by that user
     */
    public function index(User $user = null)
    {
        return Thread::when($user, fn($q) => $q->whereUserId($user->id))
            ->paginate(10, ['id', 'title']);
    }

    /**
     * Store a new Thread associated with the logged in user
     */
    public function store(CreateThreadRequest $request)
    {
        return Thread::create([...$request->validated(), 'user_id' => Auth::id()]);
    }

    /**
     * Get the Thread
     */
    public function show(Thread $thread)
    {
        return $thread;
    }

    /**
     * Update the Thread
     * @throws AuthorizationException|\Throwable
     */
    public function update(CreateThreadRequest $request, Thread $thread)
    {
        //only the creator can update it
        $this->authorize('update', $thread);

        $thread->title = $request->title;
        $thread->saveOrFail();
        return $thread;
    }

    /**
     * Delete the Thread
     * @throws AuthorizationException
     */
    public function destroy(Request $request, Thread $thread)
    {
        //only the creator can delete it
        $this->authorize('delete', $thread);

        $thread->delete();
    }
}
