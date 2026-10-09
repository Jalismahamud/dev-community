<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookmarkTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_bookmark_and_unbookmark_a_post(): void
    {
        $author = User::factory()->create();
        $user = User::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $author->id,
            'title' => 'Bookmarkable Post',
            'body_markdown' => 'Content to read later',
        ]);

        $response = $this->actingAs($user)->postJson("/posts/{$post->id}/bookmark");
        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'data' => [
                    'bookmarked' => true,
                ],
            ]);

        $this->assertDatabaseHas('bookmarks', [
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);

        $toggleResponse = $this->actingAs($user)->postJson("/posts/{$post->id}/bookmark");
        $toggleResponse->assertStatus(200)
            ->assertJson([
                'status' => true,
                'data' => [
                    'bookmarked' => false,
                ],
            ]);

        $this->assertDatabaseMissing('bookmarks', [
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);
    }
}
