<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\PostResource;
use App\Http\Resources\UserResource;
use App\Models\TechTag;
use App\Models\User;
use App\Services\ImageService;
use App\Traits\ApiResponse;
use Inertia\Inertia;

class ProfileController extends Controller
{
    use ApiResponse;

    public function edit()
    {
        $user = request()->user()->load('techTags');

        return Inertia::render('Profile', [
            'user' => new UserResource($user),
            'userTags' => $user->techTags->pluck('id')->values()->all(),
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

        if ($request->wantsJson() && ! $request->header('X-Inertia')) {
            return $this->success(new UserResource($user->fresh()), 'Profile updated.');
        }

        return back()->with('success', 'Profile updated successfully.');
    }

    public function show(User $user)
    {
        $user->load('techTags');
        $posts = $user->posts()->with(['user', 'tags', 'images'])->latest()->paginate(10);
        $isFollowing = auth()->check() ? auth()->user()->following()->where('following_id', $user->id)->exists() : false;

        return Inertia::render('UserProfile', [
            'profileUser' => new UserResource($user),
            'posts' => PostResource::collection($posts),
            'isFollowing' => $isFollowing,
            'followersCount' => $user->followers()->count(),
            'followingCount' => $user->following()->count(),
        ]);
    }
}