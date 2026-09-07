<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class CommunityNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly string $eventType, private readonly array $payload) {}

    public function via(object $notifiable): array { return ['database', 'broadcast']; }
    public function toArray(object $notifiable): array { return ['type' => $this->eventType, ...$this->payload]; }
    public function toBroadcast(object $notifiable): BroadcastMessage { return new BroadcastMessage($this->toArray($notifiable)); }
}