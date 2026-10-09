<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_author_can_update_their_post(): void
    {
        $author = User::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $author->id,
            'title' => 'Original Title',
            'body_markdown' => 'Original Content',
        ]);

        $response = $this->actingAs($author)->putJson("/posts/{$post->id}", [
            'title' => 'Updated Title',
            'body_markdown' => 'Updated Content',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'data' => [
                    'title' => 'Updated Title',
                    'body_markdown' => 'Updated Content',
                ],
            ]);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_other_user_cannot_update_post(): void
    {
        $author = User::factory()->create();
        $otherUser = User::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $author->id,
            'title' => 'Original Title',
            'body_markdown' => 'Original Content',
        ]);

        $response = $this->actingAs($otherUser)->putJson("/posts/{$post->id}", [
            'title' => 'Hacked Title',
            'body_markdown' => 'Hacked Content',
        ]);

        $response->assertStatus(403);
    }

    public function test_author_can_delete_their_post(): void
    {
        $author = User::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $author->id,
        ]);

        $response = $this->actingAs($author)->deleteJson("/posts/{$post->id}");
        $response->assertStatus(200);

        $this->assertDatabaseMissing('posts', [
            'id' => $post->id,
        ]);
    }

    public function test_other_user_cannot_delete_post(): void
    {
        $author = User::factory()->create();
        $otherUser = User::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $author->id,
        ]);

        $response = $this->actingAs($otherUser)->deleteJson("/posts/{$post->id}");
        $response->assertStatus(403);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
        ]);
    }
}
