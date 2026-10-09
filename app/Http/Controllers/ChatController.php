<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMessageRequest;
use App\Http\Resources\MessageResource;
use App\Models\Conversation;
use App\Models\User;
use App\Services\ConversationService;
use App\Traits\ApiResponse;
use Inertia\Inertia;

class ChatController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $user = request()->user();
        $conversations = $user->conversations()
            ->with([
                'participants',
                'messages' => fn ($query) => $query->with('sender')->latest()->limit(1),
            ])
            ->get();

        $activeConversationId = request()->query('conversation') 
            ?? $conversations->first()?->id;

        $initialMessages = [];
        if ($activeConversationId) {
            $activeConv = $conversations->firstWhere('id', (int) $activeConversationId);
            if ($activeConv) {
                $initialMessages = MessageResource::collection(
                    $activeConv->messages()->with('sender')->oldest()->get()
                );
            }
        }

        return Inertia::render('Chat', [
            'conversations' => $conversations,
            'activeConversationId' => $activeConversationId ? (int) $activeConversationId : null,
            'initialMessages' => $initialMessages,
        ]);
    }

    public function messages(Conversation $conversation)
    {
        abort_unless($conversation->participants()->whereKey(request()->user()->id)->exists(), 403);

        return $this->success(
            MessageResource::collection(
                $conversation->messages()->with('sender')->oldest()->get()
            )
        );
    }

    public function start(User $user, ConversationService $conversations)
    {
        return $this->success($conversations->findOrCreate(request()->user(), $user));
    }

    public function send(StoreMessageRequest $request, Conversation $conversation, ConversationService $conversations)
    {
        $message = $conversations->send(
            $request->user(),
            $conversation,
            $request->validated('body'),
            $request->file('file')
        );

        return $this->success(new MessageResource($message), 'Message sent.', 201);
    }

    public function read(Conversation $conversation)
    {
        abort_unless($conversation->participants()->whereKey(request()->user()->id)->exists(), 403);
        $conversation->messages()->whereNull('read_at')->where('sender_id', '!=', request()->user()->id)->update(['read_at' => now()]);

        return $this->success();
    }
}