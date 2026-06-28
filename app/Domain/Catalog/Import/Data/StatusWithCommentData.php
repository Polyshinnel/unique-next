<?php

namespace App\Domain\Catalog\Import\Data;

final readonly class StatusWithCommentData
{
    public function __construct(
        public string $name,
        public ?string $comment,
    ) {}
}
