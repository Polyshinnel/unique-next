<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

final class ValidImageUpload implements ValidationRule
{
    private const ALLOWED_EXTENSIONS = [
        'jpg',
        'jpeg',
        'png',
        'gif',
        'bmp',
        'webp',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile) {
            $fail(__('validation.file', ['attribute' => $attribute]));

            return;
        }

        $extension = strtolower($value->getClientOriginalExtension() ?: (string) $value->guessExtension());

        if (! in_array($extension, self::ALLOWED_EXTENSIONS, strict: true)) {
            $fail(__('validation.image', ['attribute' => $attribute]));

            return;
        }

        $contents = $value instanceof TemporaryUploadedFile
            ? $value->get()
            : file_get_contents($value->getRealPath());

        if (($contents === false) || (@getimagesizefromstring($contents) === false)) {
            $fail(__('validation.image', ['attribute' => $attribute]));
        }
    }
}
