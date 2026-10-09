<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_chat_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/chat');
        $response->assertStatus(200);
    }

    public function test_user_can_start_conversation_and_send_message(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        // Start conversation
        $startResponse = $this->actingAs($userA)->postJson("/conversations/{$userB->id}");
        $startResponse->assertStatus(200);
        $conversationId = $startResponse->json('data.id');

        // Send message
        $sendResponse = $this->actingAs($userA)->postJson("/conversations/{$conversationId}/messages", [
            'body' => 'Hello from developer A!',
        ]);
        $sendResponse->assertStatus(201);
        $sendResponse->assertJsonPath('data.body', 'Hello from developer A!');

        // Fetch messages
        $messagesResponse = $this->actingAs($userB)->getJson("/conversations/{$conversationId}/messages");
        $messagesResponse->assertStatus(200);
        $this->assertCount(1, $messagesResponse->json('data'));
    }

    public function test_user_can_send_message_with_attachment(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $startResponse = $this->actingAs($userA)->postJson("/conversations/{$userB->id}");
        $conversationId = $startResponse->json('data.id');

        $file = \Illuminate\Http\UploadedFile::fake()->image('architecture.png', 400, 300);

        $sendResponse = $this->actingAs($userA)->postJson("/conversations/{$conversationId}/messages", [
            'body' => 'Check this diagram',
            'file' => $file,
        ]);

        $sendResponse->assertStatus(201);
        $sendResponse->assertJsonPath('data.attachment_type', 'image');
        $this->assertNotNull($sendResponse->json('data.attachment_url'));
    }
}
