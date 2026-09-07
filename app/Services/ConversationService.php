<?php

namespace App\Services;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Notifications\CommunityNotification;

class ConversationService
{
    public function findOrCreate(User $first, User $second): Conversation
    {
        $conversation = Conversation::where('type', 'private')
            ->whereHas('participants', fn ($query) => $query->whereKey($first->id))
            ->whereHas('participants', fn ($query) => $query->whereKey($second->id))
            ->first();

        if ($conversation) {
            return $conversation;
        }

        $conversation = Conversation::create(['type' => 'private']);
        $conversation->participants()->attach([$first->id, $second->id], ['joined_at' => now()]);

        return $conversation;
    }

    public function send(User $sender, Conversation $conversation, string $body): Message
    {
        abort_unless($conversation->participants()->whereKey($sender->id)->exists(), 403);
        $message = $conversation->messages()->create(['sender_id' => $sender->id, 'body' => $body]);
        MessageSent::dispatch($message->load('sender'));
        $conversation->participants()->where('users.id', '!=', $sender->id)->get()->each(fn (User $recipient) => $recipient->notify(new CommunityNotification('message_sent', ['conversation_id' => $conversation->id, 'message_id' => $message->id, 'actor_id' => $sender->id, 'message' => 'You have a new message.'])));

        return $message;
    }
}