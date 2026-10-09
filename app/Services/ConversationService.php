<?php

namespace App\Services;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Notifications\CommunityNotification;
use Illuminate\Http\UploadedFile;

class ConversationService
{
    public function __construct(private readonly ImageService $imageService) {}

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

    public function send(User $sender, Conversation $conversation, ?string $body = null, ?UploadedFile $file = null): Message
    {
        abort_unless($conversation->participants()->whereKey($sender->id)->exists(), 403);

        $attachmentData = [];
        if ($file) {
            $mime = $file->getMimeType() ?? '';
            $isImage = str_starts_with($mime, 'image/');

            if ($isImage) {
                $path = $this->imageService->uploadImage($file, 'chat_attachments');
                $type = 'image';
            } else {
                $path = $file->store('chat_attachments', 'public');
                $type = 'file';
            }

            $attachmentData = [
                'attachment_path' => $path,
                'attachment_type' => $type,
                'attachment_name' => $file->getClientOriginalName(),
                'attachment_size' => $file->getSize(),
            ];
        }

        $message = $conversation->messages()->create([
            'sender_id' => $sender->id,
            'body' => $body,
            ...$attachmentData,
        ]);

        MessageSent::dispatch($message->load('sender'));

        $conversation->participants()->where('users.id', '!=', $sender->id)->get()->each(
            fn (User $recipient) => $recipient->notify(new CommunityNotification('message_sent', [
                'conversation_id' => $conversation->id,
                'message_id' => $message->id,
                'actor_id' => $sender->id,
                'message' => 'You have a new message.',
            ]))
        );

        return $message;
    }
}