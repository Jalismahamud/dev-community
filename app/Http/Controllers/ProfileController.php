<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\PostResource;
use App\Http\Resources\UserResource;
use App\Models\TechTag;
use App\Models\User;
use App\Services\ImageService;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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
            'techTags' => Cache::remember('tech_tags_all', 3600, fn () => TechTag::query()->orderBy('name')->get(['id', 'name', 'slug', 'color_hex'])),
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

        Cache::forget('user_' . $user->id . '_has_tags');
        Cache::forget('feed_top_creators');
        Cache::forget('feed_popular_tags');

        if ($request->wantsJson() && ! $request->header('X-Inertia')) {
            return $this->success(new UserResource($user->fresh()), 'Profile updated.');
        }

        return back()->with('success', 'Profile updated successfully.');
    }

    public function show(User $user)
    {
        $user->load('techTags');
        $auth = auth()->user();
        $postsQuery = $user->posts()->with(['user', 'tags', 'images']);

        if ($auth) {
            $postsQuery->withExists([
                'likes as is_liked' => fn ($q) => $q->where('user_id', $auth->id),
                'shares as is_shared' => fn ($q) => $q->where('user_id', $auth->id),
                'bookmarks as is_bookmarked' => fn ($q) => $q->where('user_id', $auth->id),
            ]);
        }

        $posts = $postsQuery->latest()->paginate(10);
        $isFollowing = $auth ? DB::table('follows')->where('follower_id', $auth->id)->where('following_id', $user->id)->exists() : false;
        $user->loadCount(['followers', 'following']);

        return Inertia::render('UserProfile', [
            'profileUser' => new UserResource($user),
            'posts' => PostResource::collection($posts),
            'isFollowing' => $isFollowing,
            'followersCount' => $user->followers_count,
            'followingCount' => $user->following_count,
        ]);
    }
}