<?php

namespace App\Enums;

enum SmsTemplate: string
{
    case Otp = 'otp';
    case OrderConfirmation = 'order_confirmation';
    case PaymentReminder = 'payment_reminder';

    public function render(array $data): string
    {
        return match ($this) {
            self::Otp => 'Your verification code is ' . ($data['code'] ?? '') . '.',
            self::OrderConfirmation => 'Your order ' . ($data['order'] ?? '') . ' has been confirmed.',
            self::PaymentReminder => 'Payment reminder for ' . ($data['reference'] ?? '') . ': ' . ($data['amount'] ?? '') . '.',
        };
    }
}