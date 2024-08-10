<?php

namespace App\Helpers\Telegram\Commands;

use App\Models\Admin;
use Illuminate\Support\Facades\Crypt;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

class StartCommand extends Command
{
    protected string $name = 'start';
    protected array $aliases = ['subscribe'];
    protected string $pattern = '{admin_id}';
    //    protected string $pattern = '{admin_id: \d+}';
    protected string $description = 'Start Command to get you started';

    public function handle(): void
    {
        dump($this);
        $fallbackUsername = $this->getUpdate()->getMessage()->from->username;
        $admin_id = $this->argument(
            'admin_id',
        );
        if (!empty($admin_id)) {
            # This will update the chat status to "typing..."
            $this->replyWithChatAction(['action' => Actions::TYPING]);
            try {
                $admin_id = Crypt::decryptString($admin_id);
                $admin_id = str_replace(config('app.name'), '', $admin_id);
                $admin = Admin::find($admin_id);
                $chat_id = $this->getUpdate()->getMessage()->chat->id;
                $admin->telegramChat()->updateOrCreate(
                    ['chatable_type' => $admin->getMorphClass(), 'chatable_id' => $admin_id],
                    ['telegram_chat_id' => $chat_id]
                );
                $this->replyWithMessage([
                    'text' => "Hello {$fallbackUsername}! Welcome to our bot :) You connected to {$admin->email} successfully. On " . config('app.name') . " platform."
                ]);
            } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                $this->replyWithMessage([
                    'text' => "Sorry, I can't decrypt this string. Please, try again."
                ]);
            }
        } else {
            $this->replyWithMessage([
                'text' => "Sorry, I can't decrypt this string. Please, try again."
            ]);
        }
        //        dump($this->getName()); //start
        //        dump($this->getUpdate()->getMessage());
    }
}
