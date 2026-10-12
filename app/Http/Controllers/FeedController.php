<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Services\PostService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class FeedController extends Controller
{
    use ApiResponse;

    public function index(Request $request, PostService $posts)
    {
        $view = $request->string('view', 'all')->toString();
        $feed = $posts->feed($request->user(), $view);

        $trendingPosts = Cache::remember('feed_trending_posts', 300, fn () => Post::query()
            ->with('user:id,name,username,avatar_path')
            ->orderByRaw('(views_count + (likes_count * 2) + (comments_count * 3) + (shares_count * 4)) DESC')
            ->limit(5)
            ->get(['id', 'code', 'slug', 'title', 'views_count', 'likes_count', 'comments_count', 'user_id', 'created_at'])
        );

        $mostViewedPosts = Cache::remember('feed_most_viewed_posts', 300, fn () => Post::query()
            ->with('user:id,name,username,avatar_path')
            ->orderByDesc('views_count')
            ->limit(5)
            ->get(['id', 'code', 'slug', 'title', 'views_count', 'likes_count', 'comments_count', 'user_id', 'created_at'])
        );

        $topCreators = Cache::remember('feed_top_creators', 600, fn () => \App\Models\User::query()
            ->withCount('posts')
            ->orderByDesc('posts_count')
            ->limit(5)
            ->get(['id', 'name', 'username', 'avatar_path', 'current_status'])
        );

        $popularTags = Cache::remember('feed_popular_tags', 600, fn () => \App\Models\TechTag::query()
            ->withCount('posts')
            ->orderByDesc('posts_count')
            ->limit(8)
            ->get(['id', 'name', 'slug', 'color_hex'])
        );

        return Inertia::render('Feed', [
            'posts' => PostResource::collection($feed),
            'view' => $view,
            'trendingPosts' => $trendingPosts,
            'mostViewedPosts' => $mostViewedPosts,
            'topCreators' => $topCreators,
            'popularTags' => $popularTags,
        ]);
    }

    public function store(StorePostRequest $request, PostService $posts)
    {
        $data = $request->validated();
        $post = $posts->create($request->user(), $data, $request->file('images', []));

        return $this->success(new PostResource($post), 'Post published.', 201);
    }

    public function update(UpdatePostRequest $request, Post $post, PostService $posts)
    {
        $updated = $posts->update($post, $request->validated());

        return $this->success(new PostResource($updated), 'Post updated.');
    }

    public function destroy(Post $post, PostService $posts)
    {
        abort_unless(request()->user()?->id === $post->user_id, 403);
        $posts->delete($post);

        return $this->success(null, 'Post deleted.');
    }

    public function show(Post $post)
    {
        $post->increment('views_count');

        $user = request()->user();
        if ($user) {
            $post->loadExists([
                'likes as is_liked' => fn ($q) => $q->where('user_id', $user->id),
                'shares as is_shared' => fn ($q) => $q->where('user_id', $user->id),
                'bookmarks as is_bookmarked' => fn ($q) => $q->where('user_id', $user->id),
            ]);
        }

        $post->load(['user', 'tags', 'images', 'comments.user']);

        return Inertia::render('PostShow', [
            'post' => new PostResource($post),
        ]);
    }

    public function impression(Post $post)
    {
        $post->increment('views_count');

        return $this->success(['views_count' => $post->views_count], 'Impression recorded.');
    }
}