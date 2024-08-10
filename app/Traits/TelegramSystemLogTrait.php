<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

trait TelegramSystemLogTrait
{
    private static string $telegram_icon_sos = 'F09F8698';
    private static string $telegram_icon_warning = 'f09f9ab8';
    private string $slackWebhook = 'https://hooks.slack.com/services/';

    //Find bot info
    //https://api.telegram.org/bot<token>/getUpdates

    public function handleException(Throwable $e): void
    {
        if (env('APP_ENV') == 'production') {
            try {
                $text = 'Server error | on ' . env('APP_NAME');
                $text = hex2bin(self::$telegram_icon_sos) . "<b>" . $text . "</b>\n";
                $text .= 'Class: ' . get_class($e) . "\n";
                $text .= 'File: ' . $e->getFile() . "\n";
                $text .= 'Line: ' . $e->getLine() . "\n";
                $text .= 'Code: ' . $e->getCode() . "\n";
                $text .= 'User/Admin ID: ' . Auth::id() . "\n";
                $text .= 'Error: ' . $e->getMessage() . "\n";
                $trace = $e->getTrace();
                for ($i = 0; $i < min(10, count($trace)); $i++) {
                    $text .= 'Trace ' . ($i + 1) . ': File: ' . $trace[$i]['file'] . ' Line: ' . $trace[$i]['line'] . "\n";
                }
                $this->sendImportantMessage($text);
            } catch (Throwable $e) {

            }
        }
    }

    public function errorMessageGroup($error = ''): void
    {
        if (env('APP_ENV') == 'production') {
            try {
                $text = 'Server error | on ' . env('APP_NAME');
                $text = hex2bin(self::$telegram_icon_sos) . "<b>" . $text . "</b>\n";
                $text .= 'Error: ' . $error ;

                $this->sendImportantMessage($text);
            } catch (Throwable $e) {

            }
        }
    }

    public function infoMessageGroup($error = ''): void
    {
        if (env('APP_ENV') == 'production') {
            try {
                $text = 'Server info | on ' . env('APP_NAME');
                $text = hex2bin(self::$telegram_icon_warning) . "<b>" . $text . "</b>\n";
                $text .= 'Info: ' . $error . "\n";
                $this->sendMinorMessage($text);

            } catch (Throwable $e) {

            }
        }
    }

    private function updateTextForTelegram($text): string
    {
        $allowed_tags = '<b><strong><i><em><u><ins><s><strike><del><a><code><pre><tg-spoiler>';
        if (strlen($text) < 500) {
            $text = strip_tags($text, $allowed_tags);
        } else {
            $text = strip_tags($text);
        }
        $text = str_replace([" < ", " > ", "&"], "", $text);
        $text = urlencode($text);
        $text = substr($text, 0, 4095);
        return $text;
    }


    private function sendImportantMessage($text): void
    {
        $this->sendTelegramChatMessage($text, config('telegram.chat_id'));
    }

    private function sendMinorMessage($text): void
    {
        $this->sendTelegramChatMessage($text, config('telegram.chat_id'));
    }

    private function sendTelegramChatMessage($text, $chat_id): void
    {
        $ch = curl_init("https://api.telegram.org/bot" . config('telegram.api_key') . "/sendMessage?parse_mode=HTML&chat_id=" . $chat_id . "&text=" . $this->updateTextForTelegram($text));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        try {
            $res = json_decode(curl_exec($ch));
            if (empty($res->ok)) {
                Log::info("Telegram Error: " . $res->description);
            }
            curl_close($ch);
        } catch (Throwable $e) {
        }
    }

    public function sendMessageSlack($text): void
    {
        $ch = curl_init($this->slackWebhook);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            "text" => strip_tags($text)
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type:application/json',
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_exec($ch);
        curl_close($ch);
    }
}
