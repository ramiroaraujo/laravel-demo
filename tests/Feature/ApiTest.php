<?php

namespace Tests\Feature;

use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    public $testCredentials = ['email' => 'setup@localhost.com', 'password' => 'validPassword1$'];

    public $otherUser;

    protected function setUp(): void
    {
        parent::setUp();

        //setup an user for authenticated requests
        $response = $this->postJson('/api/register', ['name' => 'test', ...$this->testCredentials]);

        //create a new user for permission tests
        $this->otherUser = User::factory()->create();
    }

    public function test_registration()
    {
        //test no name
        $this->postJson('/api/register', ['email' => 'validate@localhost.com', 'password' => 'validPassword1$'])
            ->assertStatus(422)
            ->assertInvalid('name');

        //test no email
        $this->postJson('/api/register', ['name' => 'test', 'password' => 'validPassword1$'])
            ->assertStatus(422)
            ->assertInvalid('email');

        //test invalid email
        $this->postJson('/api/register', ['name' => 'test', 'email' => 'invalid', 'password' => 'validPassword1$'])
            ->assertStatus(422)
            ->assertInvalid('email');

        //test multiple invalid passwords
        collect(['', 'lowercase-only', 'upercase-only', 'short', '123456789', '1234withoutSPECIALcharacters'])
            ->each(function ($p) {
                $this->postJson('/api/register', ['name' => 'name', 'email' => 'email@email.com', 'password' => $p])
                    ->assertStatus(422)
                    ->assertInvalid('password');
            });

        //test registration
        $this->postJson('/api/register', ['name' => 'test', 'email' => 'validate@localhost.com', 'password' => 'validPassword1$'])
            ->assertStatus(200)
            ->assertJson(['token' => true]);
    }

    public function test_login()
    {
        //test invalid email and password
        $this->postJson('/api/register', ['name' => 'test', 'email' => 'invalid', 'password' => ''])
            ->assertStatus(422)
            ->assertInvalid(['email', 'password']);

        //test inexistent email
        $this->postJson('/api/login', ['email' => 'inexistent@localhost.com', 'password' => 'password'])
            ->assertStatus(422)
            ->assertInvalid('email');

        //test success
        $this->postJson('/api/login', $this->testCredentials)
            ->assertStatus(200)
            ->assertJson(['token' => true]);
    }

    public function test_crud_operations_on_threads()
    {
        //create
        $response = $this->postJson('/api/threads', ['title' => 'some title'])
            ->assertStatus(201)
            ->assertJson(['id' => true]);

        $threadId = $response->json('id');

        //get
        $this->get("/api/threads/{$threadId}")
            ->assertStatus(200)
            ->assertJson(['id' => true, 'title' => true]);

        //update
        $this->putJson("/api/threads/{$threadId}", ['title' => 'updated title'])
            ->assertStatus(200)
            ->assertJsonPath('title', 'updated title');

        //delete
        $this->delete("/api/threads/{$threadId}", [])
            ->assertStatus(200);
    }

    public function test_permissions_on_threads()
    {
        //create the thread as the regular user
        $response = $this->postJson('/api/threads', ['title' => 'some title']);
        $threadId = $response->json('id');

        //get as other user
        $this->actingAs($this->otherUser)->get("/api/threads/{$threadId}")
            ->assertStatus(200)
            ->assertJson(['id' => true, 'title' => true]);

        //update as other user
        $this->actingAs($this->otherUser)->putJson("/api/threads/{$threadId}", ['title' => 'updated title'])
            ->assertStatus(403);

        //delete as other user
        $this->actingAs($this->otherUser)->delete("/api/threads/{$threadId}", [])
            ->assertStatus(403);
    }

    public function test_crud_operations_on_messages()
    {
        //create the thread to put messages
        $response = $this->postJson('/api/threads', ['title' => 'some title']);
        $threadId = $response->json('id');

        //create
        $response = $this->post("/api/threads/{$threadId}/messages", ['body' => 'some message'])
            ->assertStatus(201)
            ->assertJson(['id' => true, 'body' => true]);

        $messageId = $response->json('id');

        //update
        $this->putJson("/api/threads/{$threadId}/messages/{$messageId}", ['body' => 'updated message'])
            ->assertStatus(200)
            ->assertJsonPath('body', 'updated message');

        //delete
        $this->deleteJson("/api/threads/{$threadId}/messages/{$messageId}", [])
            ->assertStatus(200);
    }

    public function test_permissions_on_messages()
    {
        //create the thread to put messages as regular user
        $response = $this->postJson('/api/threads', ['title' => 'some title']);
        $threadId = $response->json('id');

        //create a message as regular user
        $response = $this->post("/api/threads/{$threadId}/messages", ['body' => 'some message']);
        $messageId = $response->json('id');

        //update as other user
        $this->actingAs($this->otherUser)->putJson("/api/threads/{$threadId}/messages/{$messageId}", ['title' => 'updated title'])
            ->assertStatus(403);

        //update as regular user, but with expired time
        $message = Message::find($messageId);
        $message->created_at = $message->created_at->subMinutes(10);
        $message->save();
        $this->putJson("/api/threads/{$threadId}/messages/{$messageId}", ['body' => 'cannot update'])
            ->assertStatus(403);

        //delete as other user
        $this->actingAs($this->otherUser)->delete("/api/threads/{$threadId}/messages/{$messageId}", [])
            ->assertStatus(403);
    }
}
