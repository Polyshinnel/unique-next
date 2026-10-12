<?php

namespace App\Domain\Contact\Services;

use App\Models\TelegramSetting;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

final class TelegramLeadSender
{
    /** @param array<string, string> $fields */
    public function send(string $form, array $fields): void
    {
        $settings = TelegramSetting::current();
        $token = config('services.telegram.bot_token');
        $chatId = trim((string) $settings->chat_id);

        if (! $token || ! $chatId) {
            throw new RuntimeException('Telegram is not configured.');
        }
        if ($settings->use_proxy && ! $settings->proxy_url) {
            throw new RuntimeException('Telegram proxy is enabled without an address.');
        }

        $text = "Новая заявка: {$form}\n\n";
        foreach ($fields as $label => $value) {
            $text .= $label.': '.Str::limit($value, 1500)."\n";
        }
        $text = Str::limit($text, 4000);

        $request = Http::asJson()->acceptJson()->timeout(12);
        if ($settings->use_proxy && $settings->proxy_url) {
            $request = $request->withOptions(['proxy' => $settings->proxy_url]);
        }

        try {
            $response = $request->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
            ]);
        } catch (ConnectionException $exception) {
            Log::warning('Telegram transport connection failed.', [
                'proxy_enabled' => $settings->use_proxy,
                'exception' => class_basename($exception),
            ]);

            throw new RuntimeException('Telegram connection failed.');
        }

        if (! $response->successful() || ! $response->json('ok')) {
            Log::warning('Telegram API rejected a feedback message.', [
                'status' => $response->status(),
                'description' => Str::limit((string) $response->json('description', ''), 250),
            ]);

            throw new RuntimeException('Telegram delivery failed.');
        }
    }
}
