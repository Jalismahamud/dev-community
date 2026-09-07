<?php

namespace App\Http\Resources;

use App\Http\Resources\CommentResource;
use App\Http\Resources\UserResource;
class PostResource extends BaseResource
{
    public function toArray($request): array { return ['id' => $this->id, 'title' => $this->title, 'body_markdown' => $this->body_markdown, 'likes_count' => $this->likes_count, 'comments_count' => $this->comments_count, 'created_at' => format_datetime($this->created_at), 'user' => new UserResource($this->whenLoaded('user')), 'tags' => $this->whenLoaded('tags'), 'images' => $this->whenLoaded('images'), 'comments' => CommentResource::collection($this->whenLoaded('comments'))]; }
}