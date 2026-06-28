<?php

namespace App\Domain\Catalog\Import\Resolvers;

use App\Domain\Catalog\Import\Data\StatusData;
use App\Domain\Catalog\Models\CheckStatus;
use App\Domain\Catalog\Models\DismantleStatus;
use App\Domain\Catalog\Models\EquipmentAvailability;
use App\Domain\Catalog\Models\EquipmentState;
use App\Domain\Catalog\Models\ProductStatus;
use App\Domain\Catalog\Models\ShipmentStatus;
use Illuminate\Database\Eloquent\Model;

final class StatusResolvers
{
    public function installStatus(StatusData $statusData): void
    {
        $this->shipmentStatus($statusData);
        $this->dismantleStatus($statusData);
    }

    public function productStatus(StatusData $statusData): ProductStatus
    {
        /** @var ProductStatus $status */
        $status = $this->upsertByExternalId(ProductStatus::class, $statusData->externalId, [
            'name' => $statusData->name,
            'color' => $statusData->color,
        ]);

        return $status;
    }

    public function checkStatus(StatusData $statusData): CheckStatus
    {
        /** @var CheckStatus $status */
        $status = $this->upsertByExternalId(CheckStatus::class, $statusData->externalId, [
            'name' => $statusData->name,
            'color' => $statusData->color,
        ]);

        return $status;
    }

    public function shipmentStatus(StatusData $statusData): ShipmentStatus
    {
        /** @var ShipmentStatus $status */
        $status = $this->upsertByExternalId(ShipmentStatus::class, $statusData->externalId, [
            'name' => $statusData->name,
        ]);

        return $status;
    }

    public function dismantleStatus(StatusData $statusData): DismantleStatus
    {
        /** @var DismantleStatus $status */
        $status = $this->upsertByExternalId(DismantleStatus::class, $statusData->externalId, [
            'name' => $statusData->name,
        ]);

        return $status;
    }

    public function equipmentState(int $externalId, string $name): EquipmentState
    {
        /** @var EquipmentState $state */
        $state = $this->upsertByExternalId(EquipmentState::class, $externalId, [
            'name' => $name,
        ]);

        return $state;
    }

    public function equipmentAvailability(int $externalId, string $name): EquipmentAvailability
    {
        /** @var EquipmentAvailability $availability */
        $availability = $this->upsertByExternalId(EquipmentAvailability::class, $externalId, [
            'name' => $name,
        ]);

        return $availability;
    }

    public function checkStatusByName(string $name): CheckStatus
    {
        /** @var CheckStatus $status */
        $status = $this->firstOrCreateByName(CheckStatus::class, $name);

        return $status;
    }

    public function productStatusByExternalId(int $externalId): ?ProductStatus
    {
        /** @var ?ProductStatus $status */
        $status = ProductStatus::query()
            ->where('external_id', $externalId)
            ->first();

        return $status;
    }

    public function shipmentStatusByName(string $name): ShipmentStatus
    {
        /** @var ShipmentStatus $status */
        $status = $this->firstOrCreateByName(ShipmentStatus::class, $name);

        return $status;
    }

    public function dismantleStatusByName(string $name): DismantleStatus
    {
        /** @var DismantleStatus $status */
        $status = $this->firstOrCreateByName(DismantleStatus::class, $name);

        return $status;
    }

    /**
     * @param class-string<Model> $modelClass
     */
    private function upsertByExternalId(string $modelClass, int $externalId, array $attributes): Model
    {
        /** @var Model $model */
        $model = $modelClass::firstOrNew([
            'external_id' => $externalId,
        ]);

        if (! $model->exists) {
            $model->fill($attributes)->save();

            return $model;
        }

        if ($model->only(array_keys($attributes)) !== $attributes) {
            $model->update($attributes);
        }

        return $model;
    }

    /**
     * @param class-string<Model> $modelClass
     */
    private function firstOrCreateByName(string $modelClass, string $name): Model
    {
        return $modelClass::query()->firstOrCreate([
            'name' => $name,
        ]);
    }
}
