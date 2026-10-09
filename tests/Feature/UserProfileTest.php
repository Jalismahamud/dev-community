<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_public_profile(): void
    {
        $creator = User::factory()->create([
            'name' => 'Alice Developer',
            'bio' => 'Full-stack artisan',
        ]);

        Post::factory()->count(2)->create([
            'user_id' => $creator->id,
        ]);

        $visitor = User::factory()->create();

        $response = $this->actingAs($visitor)->get("/users/{$creator->id}");
        $response->assertStatus(200);
    }

    public function test_user_can_follow_and_unfollow_creator(): void
    {
        $creator = User::factory()->create();
        $follower = User::factory()->create();

        $response = $this->actingAs($follower)->postJson("/users/{$creator->id}/follow");
        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'data' => [
                    'following' => true,
                ],
            ]);

        $this->assertDatabaseHas('follows', [
            'follower_id' => $follower->id,
            'following_id' => $creator->id,
        ]);

        $toggle = $this->actingAs($follower)->postJson("/users/{$creator->id}/follow");
        $toggle->assertStatus(200)
            ->assertJson([
                'status' => true,
                'data' => [
                    'following' => false,
                ],
            ]);

        $this->assertDatabaseMissing('follows', [
            'follower_id' => $follower->id,
            'following_id' => $creator->id,
        ]);
    }
}
