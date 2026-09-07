<?php

namespace App\Services;

use App\Events\CommentPosted;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Notifications\CommunityNotification;
use Illuminate\Support\Facades\DB;

class CommentService
{
    public function create(User $user, Post $post, array $data): Comment
    {
        $comment = DB::transaction(function () use ($user, $post, $data): Comment {
            $parent = isset($data['parent_id']) ? Comment::findOrFail($data['parent_id']) : null;
            $comment = $post->comments()->create([
                'user_id' => $user->id,
                'parent_id' => $parent?->id,
                'body' => $data['body'],
                'depth' => min(($parent?->depth ?? -1) + 1, 255),
            ]);
            $post->increment('comments_count');

            return $comment->load('user');
        });

        CommentPosted::dispatch($comment);

        if (! $post->user->is($user)) {
            $post->user->notify(new CommunityNotification('comment_posted', ['post_id' => $post->id, 'comment_id' => $comment->id, 'actor_id' => $user->id, 'message' => $user->name . ' commented on your post.']));
        }

        return $comment;
    }
}