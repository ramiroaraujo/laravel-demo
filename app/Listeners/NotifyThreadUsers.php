<?php

namespace App\Listeners;

use App\Events\MessageCreated;
use App\Jobs\SendNewMessagesNotificationToThreadUsers;
use App\Models\Message;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class NotifyThreadUsers
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(public Message $message)
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param \App\Events\MessageCreated $event
     * @return void
     */
    public function handle(MessageCreated $event)
    {
        SendNewMessagesNotificationToThreadUsers::dispatch($event->message)
            ->delay(now()->addSeconds(5));
    }
}
