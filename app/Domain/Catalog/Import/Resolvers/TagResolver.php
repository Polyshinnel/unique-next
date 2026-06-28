<?php

namespace App\Domain\Catalog\Import\Resolvers;

use App\Domain\Catalog\Models\Tag;

final class TagResolver
{
    public function resolve(string $name): Tag
    {
        return Tag::query()->firstOrCreate([
            'name' => $name,
        ]);
    }
}
