<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostShareTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_share_and_unshare_a_post(): void
    {
        $author = User::factory()->create();
        $user = User::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $author->id,
            'title' => 'First post',
            'body_markdown' => 'Hello World',
        ]);

        $response = $this->actingAs($user)->postJson("/posts/{$post->id}/share");
        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'data' => [
                    'shared' => true,
                    'shares_count' => 1,
                ],
            ]);

        $this->assertDatabaseHas('post_shares', [
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);

        $toggleResponse = $this->actingAs($user)->postJson("/posts/{$post->id}/share");
        $toggleResponse->assertStatus(200)
            ->assertJson([
                'status' => true,
                'data' => [
                    'shared' => false,
                    'shares_count' => 0,
                ],
            ]);

        $this->assertDatabaseMissing('post_shares', [
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);
    }
}
