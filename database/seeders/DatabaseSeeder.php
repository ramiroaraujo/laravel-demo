<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Message;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $users = User::factory(fake()->numberBetween(3, 5))->create();

        $threads = $users->map(fn($user) => Thread::factory(fake()->numberBetween(2, 6))->create(['user_id' => $user->id]))->flatten();

        $threads->each(fn($thread) => Message::factory(fake()->numberBetween(10, 20))->create(['thread_id' => $thread->id, 'user_id' => $users->random()->id]));
    }
}
