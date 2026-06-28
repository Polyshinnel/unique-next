<?php

namespace App\Domain\Catalog\Import\Resolvers;

use App\Domain\Catalog\Enums\ManagerRole;
use App\Domain\Catalog\Import\Data\ManagerData;
use App\Domain\Catalog\Models\Manager;
use InvalidArgumentException;

final class ManagerResolver
{
    public function upsert(ManagerData $managerData): Manager
    {
        $normalizedRole = $this->normalizeRole($managerData->role);

        if (
            $normalizedRole !== null
            && $normalizedRole !== ManagerRole::Owner->value
        ) {
            throw new InvalidArgumentException(sprintf(
                'Unsupported manager role [%s]. Only product owners can be imported.',
                $managerData->role,
            ));
        }

        $manager = Manager::firstOrNew([
            'external_id' => $managerData->externalId,
        ]);

        $attributes = [
            'name' => $managerData->name,
            'email' => $managerData->email,
            'phone' => $managerData->phone,
            'role' => $normalizedRole,
        ];

        if (! $manager->exists) {
            $manager->fill($attributes)->save();

            return $manager;
        }

        if ($manager->only(array_keys($attributes)) !== $attributes) {
            $manager->update($attributes);
        }

        return $manager;
    }

    private function normalizeRole(?string $role): ?string
    {
        return match ($role) {
            'product_owner' => ManagerRole::Owner->value,
            default => $role,
        };
    }
}
