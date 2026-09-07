<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Models\Comment;
use App\Models\Post;
use App\Services\CommentService;
use App\Services\LikeService;
use App\Traits\ApiResponse;

class PostInteractionController extends Controller
{
    use ApiResponse;

    public function like(Post $post, LikeService $likes) { return $this->success(['liked' => $likes->togglePost(request()->user(), $post), 'likes_count' => $post->fresh()->likes_count]); }
    public function likeComment(Comment $comment, LikeService $likes) { return $this->success(['liked' => $likes->toggleComment(request()->user(), $comment), 'likes_count' => $comment->fresh()->likes_count]); }
    public function comment(StoreCommentRequest $request, Post $post, CommentService $comments) { return $this->success(new \App\Http\Resources\CommentResource($comments->create($request->user(), $post, $request->validated())), 'Comment posted.', 201); }

    public function updateComment(UpdateCommentRequest $request, Comment $comment)
    {
        $comment->update(['body' => $request->validated('body'), 'is_edited' => true]);

        return $this->success(new \App\Http\Resources\CommentResource($comment->fresh()->load('user')), 'Comment updated.');
    }

    public function deleteComment(UpdateCommentRequest $request, Comment $comment)
    {
        $comment->delete();

        return $this->success(null, 'Comment deleted.');
    }
}