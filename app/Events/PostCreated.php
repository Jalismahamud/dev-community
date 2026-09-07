<?php

namespace App\Events;

use App\Models\Post;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostCreated implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Post $post) {}

    public function broadcastOn(): array { return [new Channel('feed')]; }
    public function broadcastAs(): string { return 'PostCreated'; }
    public function broadcastWith(): array { return ['post_id' => $this->post->id]; }
}