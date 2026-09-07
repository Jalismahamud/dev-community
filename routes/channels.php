<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('post.{postId}', fn ($user) => $user !== null);

Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    return $user->conversations()->whereKey($conversationId)->exists();
});

Broadcast::channel('user.{userId}', fn ($user, $userId) => (int) $user->id === (int) $userId);

Broadcast::channel('online-developers', fn ($user) => [
    'id' => $user->id,
    'name' => $user->name,
    'avatar_path' => $user->avatar_path,
]);
