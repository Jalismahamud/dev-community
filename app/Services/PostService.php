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

    public function feed(User $user, string $view = 'all')
    {
        return Post::query()
            ->with(['user', 'tags', 'images', 'comments.user'])
            ->when($view === 'following', fn ($query) => $query->whereIn('user_id', $user->following()->select('users.id')))
            ->when($view === 'my-stack', fn ($query) => $query->whereHas('tags', fn ($tags) => $tags->whereIn('tech_tags.id', $user->techTags()->select('tech_tags.id'))))
            ->when($view === 'trending', fn ($query) => $query->where('created_at', '>=', now()->subDay())->orderByRaw('(likes_count + comments_count) DESC'))
            ->latest('posts.created_at')
            ->cursorPaginate(12)
            ->withQueryString();
    }
}