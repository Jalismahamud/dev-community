<?php

namespace App\Http\Resources;

class MessageResource extends BaseResource
{
    public function toArray($request): array { return ['id' => $this->id, 'conversation_id' => $this->conversation_id, 'body' => $this->body, 'read_at' => format_datetime($this->read_at), 'created_at' => format_datetime($this->created_at), 'sender' => new UserResource($this->whenLoaded('sender'))]; }
}