<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Services\ImageService;
use App\Traits\ApiResponse;
use App\Models\TechTag;
use Inertia\Inertia;

class ProfileController extends Controller
{
    use ApiResponse;

    public function edit()
    {
        return Inertia::render('Profile', [
            'user' => new UserResource(request()->user()),
            'techTags' => TechTag::query()->orderBy('name')->get(['id', 'name', 'slug', 'color_hex']),
        ]);
    }

    public function update(UpdateProfileRequest $request, ImageService $images)
    {
        $user = $request->user();
        $data = $request->safe()->except(['tag_ids', 'avatar']);

        if ($request->hasFile('avatar')) {
            $data['avatar_path'] = $images->replaceImage($request->file('avatar'), $user->avatar_path, 'avatars');
        }

        $user->update($data);
        $user->techTags()->sync($request->validated('tag_ids', []));

        return $this->success(new UserResource($user->fresh()), 'Profile updated.');
    }
}