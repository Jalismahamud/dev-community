<?php

namespace App\Http\Resources;

use App\Http\Resources\UserResource;
class CommentResource extends BaseResource
{
    public function toArray($request): array { return ['id' => $this->id, 'post_id' => $this->post_id, 'parent_id' => $this->parent_id, 'body' => $this->trashed() ? '[deleted]' : $this->body, 'depth' => $this->depth, 'likes_count' => $this->likes_count, 'is_edited' => $this->is_edited, 'user' => new UserResource($this->whenLoaded('user')), 'created_at' => format_datetime($this->created_at)]; }
}