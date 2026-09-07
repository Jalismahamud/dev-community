<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostLiked implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly int $postId, public readonly int $count, public readonly int $userId) {}
    public function broadcastOn(): array { return [new PrivateChannel('post.' . $this->postId)]; }
    public function broadcastAs(): string { return 'PostLiked'; }
    public function broadcastWith(): array { return ['post_id' => $this->postId, 'likes_count' => $this->count, 'user_id' => $this->userId]; }
}