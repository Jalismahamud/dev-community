<?php

namespace App\Http\Resources;

use App\Http\Resources\CommentResource;
use App\Http\Resources\UserResource;
class PostResource extends BaseResource
{
    public function toArray($request): array
    {
        $user = $request->user();

        return [
            'id' => $this->id,
            'code' => $this->code,
            'slug' => $this->slug,
            'title' => $this->title,
            'body_markdown' => $this->body_markdown,
            'likes_count' => $this->likes_count,
            'comments_count' => $this->comments_count,
            'shares_count' => $this->shares_count ?? 0,
            'views_count' => $this->views_count ?? 0,
            'is_liked' => $user ? (isset($this->is_liked) ? (bool) $this->is_liked : $this->likes()->where('user_id', $user->id)->exists()) : false,
            'is_shared' => $user ? (isset($this->is_shared) ? (bool) $this->is_shared : $this->shares()->where('user_id', $user->id)->exists()) : false,
            'is_bookmarked' => $user ? (isset($this->is_bookmarked) ? (bool) $this->is_bookmarked : $this->bookmarks()->where('user_id', $user->id)->exists()) : false,
            'created_at' => format_datetime($this->created_at),
            'user' => new UserResource($this->whenLoaded('user')),
            'tags' => $this->whenLoaded('tags'),
            'images' => $this->whenLoaded('images'),
            'comments' => CommentResource::collection($this->whenLoaded('comments')),
        ];
    }
}