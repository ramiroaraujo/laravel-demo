<?php /** @var \App\Models\Thread $thread */ ?><!---->

<h3>There are new messages in {{$thread->title}}</h3>

@foreach($thread->messages as $message)
    <div>
        <p>Message from {{$message->user->name}} on <em>{{$message->created_at->toFormattedDateString()}}</em></p>
        <q>{{$message->body}}</q>
    </div>
    <hr>
@endforeach
