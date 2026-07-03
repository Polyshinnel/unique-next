<?php

namespace Tests\Unit;

use App\Rules\ValidImageUpload;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

final class ValidImageUploadTest extends TestCase
{
    public function test_it_keeps_backward_compatible_extensions_by_default(): void
    {
        $rule = new ValidImageUpload();
        $failureMessages = [];

        $rule->validate('image', UploadedFile::fake()->image('banner.GIF'), function (string $message) use (&$failureMessages): void {
            $failureMessages[] = $message;
        });

        self::assertSame([], $failureMessages);
    }

    public function test_it_allows_custom_extension_lists_with_case_insensitive_matching(): void
    {
        $rule = new ValidImageUpload(['JPG', 'JPEG', 'PNG', 'WEBP']);
        $allowedFailureMessages = [];
        $blockedFailureMessages = [];

        $rule->validate('image', UploadedFile::fake()->image('banner.JPEG'), function (string $message) use (&$allowedFailureMessages): void {
            $allowedFailureMessages[] = $message;
        });

        $rule->validate('image', UploadedFile::fake()->image('banner.gif'), function (string $message) use (&$blockedFailureMessages): void {
            $blockedFailureMessages[] = $message;
        });

        self::assertSame([], $allowedFailureMessages);
        self::assertCount(1, $blockedFailureMessages);
    }

    public function test_it_rejects_invalid_image_bytes_even_with_an_allowed_extension(): void
    {
        $rule = new ValidImageUpload(['jpg', 'jpeg', 'png', 'webp']);
        $failureMessages = [];

        $rule->validate('image', UploadedFile::fake()->createWithContent('broken.jpg', 'not-an-image'), function (string $message) use (&$failureMessages): void {
            $failureMessages[] = $message;
        });

        self::assertCount(1, $failureMessages);
    }
}
