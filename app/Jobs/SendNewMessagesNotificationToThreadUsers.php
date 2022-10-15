<?php

namespace App\Jobs;

use App\Mail\NewMessages;
use App\Models\Message;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendNewMessagesNotificationToThreadUsers implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(private Message $message)
    {
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $users = Message::whereThreadId($this->message->thread_id)
            ->with('user')
            ->groupBy('user_id')
            ->select(['user_id'])
            ->get()
            ->map(fn(Message $m) => $m->user)
            //eager load and add thread creator
            ->push($this->message->thread->user)
            //ensure thread creator is not duplicated
            ->unique('id');

        $thread = Thread::whereId($this->message->thread_id)
            ->with(['messages' => function (Relation $q) {
                return $q
                    ->where('id', '>=', $this->message->id)
                    ->with('user');
            }])
            ->first();

        //if all the msgs are from one user, remove it from notification list
        if ($thread->messages->unique('user_id')->count() === 1) {
            $users = $users->reject(fn(User $u) => $u->id === $thread->messages->first()->user_id);
        }

        //notify users by email
        $users->each(fn(User $u) => Mail::to($u)->send(new NewMessages($thread)));
    }

    public function uniqueId()
    {
        return $this->message->thread_id;
    }
}
