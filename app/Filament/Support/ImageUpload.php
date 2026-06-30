<?php

namespace App\Filament\Support;

use App\Rules\ValidImageUpload;
use Illuminate\Contracts\Validation\ValidationRule;

final class ImageUpload
{
    public const ACCEPT_ATTRIBUTE = 'image/jpeg,image/png,image/gif,image/bmp,image/webp,.jpg,.jpeg,.png,.gif,.bmp,.webp';

    public static function rule(): ValidationRule
    {
        return new ValidImageUpload();
    }
}
