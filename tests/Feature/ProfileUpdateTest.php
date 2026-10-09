<?php

namespace Tests\Feature;

use App\Models\TechTag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_profile_edit_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/profile');
        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_update_profile_info(): void
    {
        $user = User::factory()->create();
        $tag = TechTag::create(['name' => 'Laravel', 'slug' => 'laravel', 'color_hex' => '#FF2D20']);

        $response = $this->actingAs($user)->post('/profile', [
            'name' => 'Updated Developer Name',
            'current_status' => 'Staff Platform Architect',
            'bio' => 'Building scalable event systems.',
            'github_url' => 'https://github.com/updated-dev',
            'linkedin_url' => 'https://linkedin.com/in/updated-dev',
            'tag_ids' => [$tag->id],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Developer Name',
            'current_status' => 'Staff Platform Architect',
        ]);
        $this->assertTrue($user->fresh()->techTags->contains($tag->id));
    }
}
