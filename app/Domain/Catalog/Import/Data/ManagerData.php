<?php

namespace App\Domain\Catalog\Import\Data;

final readonly class ManagerData
{
    public function __construct(
        public int $externalId,
        public string $name,
        public ?string $email,
        public ?string $phone,
        public ?string $role,
    ) {}
}
