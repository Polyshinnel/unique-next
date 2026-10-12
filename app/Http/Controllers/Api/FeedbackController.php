<?php

namespace App\Http\Controllers\Api;

use App\Domain\Contact\Services\TelegramLeadSender;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

final class FeedbackController
{
    public function __invoke(Request $request, TelegramLeadSender $sender): JsonResponse
    {
        $validated = $request->validate([
            'form' => ['required', 'string', 'in:Обратная связь,Заявка на импорт,Заявка на выкуп,Обратный звонок'],
            'fields' => ['required', 'array', 'min:1', 'max:10'],
            'fields.*' => ['nullable', 'string', 'max:2000'],
        ]);

        $fields = array_filter($validated['fields'], fn ($value) => filled($value));
        if ($fields === []) {
            return response()->json(['message' => 'Заполните хотя бы одно поле.'], 422);
        }

        try {
            $sender->send($validated['form'], $fields);
        } catch (Throwable) {
            Log::warning('Telegram feedback delivery failed.', ['form' => $validated['form']]);

            return response()->json(['message' => 'Не удалось отправить заявку. Попробуйте ещё раз.'], 502);
        }

        return response()->json(['message' => 'Заявка отправлена.']);
    }
}
