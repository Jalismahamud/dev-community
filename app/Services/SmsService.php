<?php

namespace App\Services;

use App\Enums\SmsTemplate;
use Illuminate\Support\Facades\Http;

class SmsService
{
    public function send(string $phone, string $message): bool
    {
        $response = Http::timeout(15)->withToken(config('sms.api_key'))->post(config('sms.api_url'), [
            'sender_id' => config('sms.sender_id'),
            'phone' => $phone,
            'message' => $message,
        ]);

        return $response->successful();
    }

    public function sendTemplate(string $phone, SmsTemplate $template, array $data = []): bool
    {
        return $this->send($phone, $template->render($data));
    }
}