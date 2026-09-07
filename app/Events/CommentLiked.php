<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentLiked implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly int $postId, public readonly int $commentId, public readonly int $count, public readonly int $userId) {}
    public function broadcastOn(): array { return [new PrivateChannel('post.' . $this->postId)]; }
    public function broadcastAs(): string { return 'CommentLiked'; }
    public function broadcastWith(): array { return ['comment_id' => $this->commentId, 'likes_count' => $this->count, 'user_id' => $this->userId]; }
}