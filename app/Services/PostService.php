<?php

namespace App\Services;

use App\Events\PostCreated;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class PostService
{
    public function __construct(private readonly ImageService $imageService) {}

    public function create(User $user, array $data, array $images = []): Post
    {
        $post = DB::transaction(function () use ($user, $data, $images): Post {
            $post = $user->posts()->create([
                'title' => $data['title'] ?? null,
                'body_markdown' => $data['body_markdown'],
            ]);

            $post->tags()->sync($data['tag_ids'] ?? []);

            foreach ($images as $sortOrder => $image) {
                if ($image instanceof UploadedFile) {
                    $post->images()->create([
                        'uploaded_by' => $user->id,
                        'path_webp' => $this->imageService->uploadImage($image, 'posts'),
                        'sort_order' => $sortOrder,
                    ]);
                }
            }

            return $post->load(['user', 'tags', 'images']);
        });

        PostCreated::dispatch($post);

        return $post;
    }

    public function update(Post $post, array $data): Post
    {
        return DB::transaction(function () use ($post, $data): Post {
            $post->update([
                'title' => $data['title'] ?? null,
                'body_markdown' => $data['body_markdown'],
            ]);

            if (isset($data['tag_ids'])) {
                $post->tags()->sync($data['tag_ids']);
            }

            return $post->load(['user', 'tags', 'images']);
        });
    }

    public function delete(Post $post): void
    {
        DB::transaction(function () use ($post): void {
            foreach ($post->images as $image) {
                $this->imageService->deleteImage($image->path_webp);
            }
            $post->delete();
        });
    }

    public function feed(User $user, string $view = 'all')
    {
        $query = Post::query()->with(['user', 'tags', 'images', 'comments.user']);

        match ($view) {
            'following' => $query->whereIn('user_id', $user->following()->select('users.id'))->latest('posts.created_at'),
            'my-stack' => $query->whereHas('tags', fn ($tags) => $tags->whereIn('tech_tags.id', $user->techTags()->select('tech_tags.id')))->latest('posts.created_at'),
            'bookmarks' => $query->whereIn('posts.id', $user->bookmarks()->select('post_id'))->latest('posts.created_at'),
            'today' => $query->whereDate('posts.created_at', now()->today())->latest('posts.created_at'),
            'most-viewed', 'most_viewed' => $query->orderByDesc('views_count'),
            'trending', 'popular' => $query->orderByRaw('(views_count + (likes_count * 2) + (comments_count * 3) + (shares_count * 4)) DESC'),
            default => $query->latest('posts.created_at'),
        };

        return $query->cursorPaginate(12)->withQueryString();
    }
}