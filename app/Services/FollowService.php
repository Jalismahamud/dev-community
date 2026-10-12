<?php

namespace App\Services;

use App\Models\Follow;
use App\Models\User;
use App\Notifications\CommunityNotification;

class FollowService
{
    public function toggle(User $follower, User $following): bool
    {
        abort_if($follower->is($following), 422, 'You cannot follow yourself.');
        $follow = Follow::where(['follower_id' => $follower->id, 'following_id' => $following->id])->first();

        if ($follow) {
            $follow->delete();
            \Illuminate\Support\Facades\Cache::forget('feed_top_creators');

            return false;
        }

        Follow::create(['follower_id' => $follower->id, 'following_id' => $following->id]);
        \Illuminate\Support\Facades\Cache::forget('feed_top_creators');
        $following->notify(new CommunityNotification('followed', ['actor_id' => $follower->id, 'message' => $follower->name . ' followed you.']));

        return true;
    }
}