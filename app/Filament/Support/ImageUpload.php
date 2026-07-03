<?php

namespace App\Filament\Support;

use App\Rules\ValidImageUpload;
use Filament\Forms\Components\FileUpload;
use Illuminate\Contracts\Validation\ValidationRule;

final class ImageUpload
{
    public const ACCEPT_ATTRIBUTE = 'image/jpeg,image/png,image/gif,image/bmp,image/webp,.jpg,.jpeg,.png,.gif,.bmp,.webp';

    public const ACCEPT_WEBP_CONVERTIBLE_ATTRIBUTE = 'image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp';

    public static function rule(): ValidationRule
    {
        return new ValidImageUpload();
    }

    public static function webpConvertibleRule(): ValidationRule
    {
        return new ValidImageUpload(['jpg', 'jpeg', 'png', 'webp']);
    }

    public static function webpConvertibleUpload(FileUpload $upload, string $disk, string $directory): FileUpload
    {
        return $upload
            ->disk($disk)
            ->directory($directory)
            ->visibility('public')
            ->rules([self::webpConvertibleRule()])
            ->extraInputAttributes([
                'accept' => self::ACCEPT_WEBP_CONVERTIBLE_ATTRIBUTE,
            ]);
    }
}
