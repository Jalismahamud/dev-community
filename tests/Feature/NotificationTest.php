<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\CommunityNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_notifications(): void
    {
        $user = User::factory()->create();
        $user->notify(new CommunityNotification('welcome', ['message' => 'Welcome to dev-community!']));

        $response = $this->actingAs($user)->get('/notifications');
        $response->assertStatus(200);

        $jsonResponse = $this->actingAs($user)->getJson('/notifications');
        $jsonResponse->assertStatus(200)
            ->assertJson([
                'status' => true,
            ]);
    }

    public function test_user_can_mark_notification_as_read(): void
    {
        $user = User::factory()->create();
        $user->notify(new CommunityNotification('welcome', ['message' => 'Welcome to dev-community!']));

        $notificationId = $user->notifications()->first()->id;

        $response = $this->actingAs($user)->postJson("/notifications/{$notificationId}/read");
        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
            ]);

        $this->assertNotNull($user->notifications()->first()->read_at);
    }
}
