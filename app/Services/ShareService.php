<?php

namespace App\Services;

use App\Events\PostShared;
use App\Models\Post;
use App\Models\PostShare;
use App\Models\User;
use App\Notifications\CommunityNotification;
use Illuminate\Support\Facades\DB;

class ShareService
{
    public function toggle(User $user, Post $post, ?string $quote = null): bool
    {
        return DB::transaction(function () use ($user, $post, $quote): bool {
            $share = PostShare::where(['post_id' => $post->id, 'user_id' => $user->id])->first();
            $shared = $share === null;

            if ($shared) {
                PostShare::create([
                    'post_id' => $post->id,
                    'user_id' => $user->id,
                    'quote_body' => $quote,
                ]);
                $post->increment('shares_count');
            } else {
                $share->delete();
                $post->decrement('shares_count');
            }

            PostShared::dispatch($post->id, $post->fresh()->shares_count, $user->id);

            if ($shared && ! $post->user->is($user)) {
                $post->user->notify(new CommunityNotification('post_shared', [
                    'post_id' => $post->id,
                    'actor_id' => $user->id,
                    'message' => $user->name . ' shared your post.',
                ]));
            }

            return $shared;
        });
    }
}
