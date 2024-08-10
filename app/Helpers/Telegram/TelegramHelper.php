<?php

declare(strict_types=1);

namespace App\Helpers\Telegram;

use Illuminate\Support\Facades\Cache;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramHelper
{
    public static function getTelegramToken(): string
    {
        return config('telegram.api_key');
    }
    public static function getBotUsername(): string
    {
        return Cache::remember('telegram_username', 60 * 5, function () {
            $token = self::getTelegramToken();
            if (empty($token)) {
                return '';
            }
            Telegram::setAccessToken($token);
            return Telegram::getMe()->username;
        });
    }
}
