<?php

namespace App\Models;

use App\Events\MessageCreated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Arr;
use Laravel\Scout\Searchable;

/**
 *
 */
class Message extends Model
{
    use HasFactory, Searchable, Notifiable;

    protected $fillable = ['user_id', 'thread_id', 'body'];

    protected $hidden = ['thread_id', 'user_id', 'updated_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function thread()
    {
        return $this->belongsTo(Thread::class);
    }

    public function searchableAs()
    {
        return 'messages_index';
    }

    public function toSearchableArray()
    {
        return [
            'user_id' => $this->user_id,
            'body' => $this->body
        ];
    }

    protected $dispatchesEvents = ['created' => MessageCreated::class];
}
