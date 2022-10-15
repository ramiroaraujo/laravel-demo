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
     * Display a listing of the resource.
     */
    public function index(User $user = null)
    {
        return Thread::when($user, fn($q) => $q->whereUserId($user->id))
            ->paginate(10, ['id', 'title']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateThreadRequest $request)
    {
        return Thread::create([...$request->validated(), 'user_id' => Auth::id()]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Thread $thread)
    {
        return $thread;
    }


    /**
     * Update the specified resource in storage.
     * @throws AuthorizationException|\Throwable
     */
    public function update(CreateThreadRequest $request, Thread $thread)
    {
        $this->authorize('update', $thread);

        $thread->title = $request->title;
        $thread->saveOrFail();
        return $thread;
    }

    /**
     * Remove the specified resource from storage.
     * @throws AuthorizationException
     */
    public function destroy(Request $request, Thread $thread)
    {
        $this->authorize('delete', $thread);

        $thread->delete();
    }
}
