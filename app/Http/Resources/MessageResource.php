<?php

namespace App\Http\Resources;

class MessageResource extends BaseResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'conversation_id' => $this->conversation_id,
            'body' => $this->body,
            'attachment_url' => $this->attachment_url,
            'attachment_type' => $this->attachment_type,
            'attachment_name' => $this->attachment_name,
            'attachment_size' => $this->attachment_size,
            'read_at' => format_datetime($this->read_at),
            'created_at' => format_datetime($this->created_at),
            'sender' => new UserResource($this->whenLoaded('sender')),
        ];
    }
}