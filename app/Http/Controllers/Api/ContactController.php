<?php

namespace App\Http\Controllers\Api;

use App\Domain\Contact\Models\Contact;
use Illuminate\Http\JsonResponse;

final class ContactController
{
    public function __invoke(): JsonResponse
    {
        $contact = Contact::query()->oldest('id')->first();

        return response()->json($contact?->only([
            'address',
            'address2',
            'phone',
            'email',
            'work_schedule',
            'work_schedule2',
            'latitude',
            'longitude',
            'telegram',
            'vk',
            'max',
            'inn',
            'ogrn',
        ]) ?? []);
    }
}
