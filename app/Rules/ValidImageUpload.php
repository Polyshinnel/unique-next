<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

final class ValidImageUpload implements ValidationRule
{
    /**
     * @param  array<int, string>  $allowedExtensions
     */
    public function __construct(
        private readonly array $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'],
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile) {
            $fail(__('validation.file', ['attribute' => $attribute]));

            return;
        }

        $extension = strtolower($value->getClientOriginalExtension() ?: (string) $value->guessExtension());
        $allowedExtensions = array_map(
            static fn (string $extension): string => strtolower($extension),
            $this->allowedExtensions,
        );

        if (! in_array($extension, $allowedExtensions, strict: true)) {
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
