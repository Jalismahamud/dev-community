<?php

namespace App\Services;

use App\Events\CommentLiked;
use App\Events\PostLiked;
use App\Models\Comment;
use App\Models\CommentLike;
use App\Models\Post;
use App\Models\PostLike;
use App\Models\User;
use App\Notifications\CommunityNotification;
use Illuminate\Support\Facades\DB;

class LikeService
{
    public function togglePost(User $user, Post $post): bool
    {
        return DB::transaction(function () use ($user, $post): bool {
            $like = PostLike::where(['post_id' => $post->id, 'user_id' => $user->id])->first();
            $liked = $like === null;

            if ($liked) {
                PostLike::create(['post_id' => $post->id, 'user_id' => $user->id]);
                $post->increment('likes_count');
            } else {
                $like->delete();
                $post->decrement('likes_count');
            }

            PostLiked::dispatch($post->id, $post->fresh()->likes_count, $user->id);

            if ($liked && ! $post->user->is($user)) {
                $post->user->notify(new CommunityNotification('post_liked', ['post_id' => $post->id, 'actor_id' => $user->id, 'message' => $user->name . ' liked your post.']));
            }

            return $liked;
        });
    }

    public function toggleComment(User $user, Comment $comment): bool
    {
        return DB::transaction(function () use ($user, $comment): bool {
            $like = CommentLike::where(['comment_id' => $comment->id, 'user_id' => $user->id])->first();
            $liked = $like === null;

            if ($liked) {
                CommentLike::create(['comment_id' => $comment->id, 'user_id' => $user->id]);
                $comment->increment('likes_count');
            } else {
                $like->delete();
                $comment->decrement('likes_count');
            }

            CommentLiked::dispatch($comment->post_id, $comment->id, $comment->fresh()->likes_count, $user->id);

            if ($liked && ! $comment->user->is($user)) {
                $comment->user->notify(new CommunityNotification('comment_liked', ['comment_id' => $comment->id, 'actor_id' => $user->id, 'message' => $user->name . ' liked your comment.']));
            }

            return $liked;
        });
    }
}