<?php

namespace App\Console\Commands;

use App\Helpers\Telegram\TelegramHelper;
use Illuminate\Console\Command;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramHandlerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:telegram-handler';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $token = TelegramHelper::getTelegramToken();
        if (!empty($token)) {
            Telegram::setAccessToken($token);
            Telegram::commandsHandler();
        }
    }
}
