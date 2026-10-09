<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_single_post_can_be_viewed(): void
    {
        $author = User::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $author->id,
            'title' => 'Single Post View',
            'body_markdown' => 'Detailed article content',
        ]);

        $response = $this->get("/posts/{$post->id}");
        $response->assertStatus(200);
    }
}
