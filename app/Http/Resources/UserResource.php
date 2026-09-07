<?php

namespace App\Http\Resources;

class UserResource extends BaseResource
{
    public function toArray($request): array { return ['id' => $this->id, 'name' => $this->name, 'avatar_path' => $this->avatar_path, 'bio' => $this->bio, 'github_url' => $this->github_url, 'linkedin_url' => $this->linkedin_url, 'current_status' => $this->current_status, 'profile_completion' => $this->profile_completion]; }
}