<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostShared implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly int $postId, public readonly int $count, public readonly int $userId) {}
    public function broadcastOn(): array { return [new PrivateChannel('post.' . $this->postId)]; }
    public function broadcastAs(): string { return 'PostShared'; }
    public function broadcastWith(): array { return ['post_id' => $this->postId, 'shares_count' => $this->count, 'user_id' => $this->userId]; }
}
