<?php

declare(strict_types=1);

namespace App\Models\Traits;

use App\Models\TelegramChat;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasTelegramChat
{
    /**
     * Get the entity's notifications.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function telegramChat(): MorphOne
    {
        return $this->morphOne(TelegramChat::class, 'chatable');
    }

    /**
     * @param null $notification
     * @return string|null
     */
    public function routeNotificationForTelegram($notification = null): ?string
    {
        $chat = $this->telegramChat()->first();
        return $chat?->telegram_chat_id;
    }
}
