<?php

namespace App\Services;

use App\Models\Bookmark;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BookmarkService
{
    public function toggle(User $user, Post $post): bool
    {
        return DB::transaction(function () use ($user, $post): bool {
            $bookmark = Bookmark::where(['post_id' => $post->id, 'user_id' => $user->id])->first();
            $bookmarked = $bookmark === null;

            if ($bookmarked) {
                Bookmark::create([
                    'post_id' => $post->id,
                    'user_id' => $user->id,
                ]);
            } else {
                $bookmark->delete();
            }

            return $bookmarked;
        });
    }
}
