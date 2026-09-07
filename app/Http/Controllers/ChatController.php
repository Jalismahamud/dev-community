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
        $conversations = request()->user()->conversations()->with(['participants', 'messages' => fn ($query) => $query->latest()->limit(1)])->get();

        return Inertia::render('Chat', ['conversations' => $conversations]);
    }

    public function start(User $user, ConversationService $conversations) { return $this->success($conversations->findOrCreate(request()->user(), $user)); }
    public function send(StoreMessageRequest $request, Conversation $conversation, ConversationService $conversations) { return $this->success(new MessageResource($conversations->send($request->user(), $conversation, $request->validated('body'))), 'Message sent.', 201); }
    public function read(Conversation $conversation) { abort_unless($conversation->participants()->whereKey(request()->user()->id)->exists(), 403); $conversation->messages()->whereNull('read_at')->where('sender_id', '!=', request()->user()->id)->update(['read_at' => now()]); return $this->success(); }
}