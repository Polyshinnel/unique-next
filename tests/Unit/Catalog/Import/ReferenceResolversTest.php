<?php

namespace Tests\Unit\Catalog\Import;

use App\Domain\Catalog\Enums\ManagerRole;
use App\Domain\Catalog\Import\Data\CategoryData;
use App\Domain\Catalog\Import\Data\ManagerData;
use App\Domain\Catalog\Import\Data\RegionData;
use App\Domain\Catalog\Import\Data\StatusData;
use App\Domain\Catalog\Import\Resolvers\CategoryResolver;
use App\Domain\Catalog\Import\Resolvers\ManagerResolver;
use App\Domain\Catalog\Import\Resolvers\RegionResolver;
use App\Domain\Catalog\Import\Resolvers\StatusResolvers;
use App\Domain\Catalog\Import\Resolvers\TagResolver;
use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\CheckStatus;
use App\Domain\Catalog\Models\DismantleStatus;
use App\Domain\Catalog\Models\EquipmentAvailability;
use App\Domain\Catalog\Models\EquipmentState;
use App\Domain\Catalog\Models\Manager;
use App\Domain\Catalog\Models\ProductStatus;
use App\Domain\Catalog\Models\Region;
use App\Domain\Catalog\Models\ShipmentStatus;
use App\Domain\Catalog\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ReferenceResolversTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_resolver_upserts_and_links_parents_in_two_passes(): void
    {
        $resolver = new CategoryResolver();
        $categories = [
            new CategoryData(externalId: 20, name: 'Колёсные погрузчики', parentExternalId: 10),
            new CategoryData(externalId: 10, name: 'Спецтехника', parentExternalId: null),
        ];

        $resolver->upsertMany($categories);
        $resolver->linkParents($categories);

        self::assertDatabaseCount('categories', 2);
        self::assertSame('spetstehnika', Category::query()->where('external_id', 10)->value('slug'));
        self::assertSame(
            Category::query()->where('external_id', 10)->value('id'),
            Category::query()->where('external_id', 20)->value('parent_id'),
        );

        $resolver->upsertMany([
            new CategoryData(externalId: 10, name: 'Строительная техника', parentExternalId: null),
            new CategoryData(externalId: 20, name: 'Фронтальные погрузчики', parentExternalId: 10),
        ]);
        $resolver->linkParents([
            new CategoryData(externalId: 10, name: 'Строительная техника', parentExternalId: null),
            new CategoryData(externalId: 20, name: 'Фронтальные погрузчики', parentExternalId: 10),
        ]);

        self::assertDatabaseCount('categories', 2);
        self::assertDatabaseHas('categories', [
            'external_id' => 10,
            'name' => 'Строительная техника',
            'slug' => 'spetstehnika',
        ]);
        self::assertDatabaseHas('categories', [
            'external_id' => 20,
            'name' => 'Фронтальные погрузчики',
        ]);
    }

    public function test_status_resolvers_are_idempotent_and_support_name_matching(): void
    {
        $resolver = new StatusResolvers();

        $resolver->productStatus(new StatusData(externalId: 4, name: 'Активно', color: '#00ff00'));
        $resolver->checkStatus(new StatusData(externalId: 5, name: 'Проверено', color: '#112233'));
        $resolver->installStatus(new StatusData(externalId: 6, name: 'Готово к погрузке', color: '#ffffff'));
        $resolver->equipmentState(15, 'Б/у');
        $resolver->equipmentAvailability(21, 'В наличии');

        $resolver->productStatus(new StatusData(externalId: 4, name: 'Опубликовано', color: '#000000'));
        $resolver->checkStatus(new StatusData(externalId: 5, name: 'Осмотрено', color: '#445566'));
        $resolver->shipmentStatus(new StatusData(externalId: 6, name: 'Погрузка согласована', color: null));
        $resolver->dismantleStatus(new StatusData(externalId: 6, name: 'Демонтаж согласован', color: null));
        $resolver->equipmentState(15, 'Новое');
        $resolver->equipmentAvailability(21, 'Под заказ');

        $checkByName = $resolver->checkStatusByName('Осмотрено');
        $shipmentByName = $resolver->shipmentStatusByName('Погрузка согласована');
        $dismantleByName = $resolver->dismantleStatusByName('Демонтаж согласован');
        $newShipmentByName = $resolver->shipmentStatusByName('Нужна погрузка');

        self::assertSame(1, ProductStatus::query()->count());
        self::assertSame(1, CheckStatus::query()->count());
        self::assertSame(2, ShipmentStatus::query()->count());
        self::assertSame(1, DismantleStatus::query()->count());
        self::assertSame(1, EquipmentState::query()->count());
        self::assertSame(1, EquipmentAvailability::query()->count());

        self::assertDatabaseHas('product_statuses', [
            'external_id' => 4,
            'name' => 'Опубликовано',
            'color' => '#000000',
        ]);
        self::assertDatabaseHas('check_statuses', [
            'external_id' => 5,
            'name' => 'Осмотрено',
            'color' => '#445566',
        ]);
        self::assertDatabaseHas('shipment_statuses', [
            'external_id' => 6,
            'name' => 'Погрузка согласована',
        ]);
        self::assertDatabaseHas('shipment_statuses', [
            'name' => 'Нужна погрузка',
            'external_id' => null,
        ]);
        self::assertDatabaseHas('dismantle_statuses', [
            'external_id' => 6,
            'name' => 'Демонтаж согласован',
        ]);
        self::assertDatabaseHas('equipment_states', [
            'external_id' => 15,
            'name' => 'Новое',
        ]);
        self::assertDatabaseHas('equipment_availabilities', [
            'external_id' => 21,
            'name' => 'Под заказ',
        ]);

        self::assertSame($checkByName->id, CheckStatus::query()->where('external_id', 5)->value('id'));
        self::assertSame($shipmentByName->id, ShipmentStatus::query()->where('external_id', 6)->value('id'));
        self::assertSame($dismantleByName->id, DismantleStatus::query()->where('external_id', 6)->value('id'));
        self::assertNull($newShipmentByName->external_id);
    }

    public function test_manager_region_and_tag_resolvers_are_idempotent(): void
    {
        $managerResolver = new ManagerResolver();
        $regionResolver = new RegionResolver();
        $tagResolver = new TagResolver();

        $manager = $managerResolver->upsert(new ManagerData(
            externalId: 501,
            name: 'Иван Иванов',
            email: 'ivan@example.com',
            phone: '+7 999 000-00-00',
            role: ManagerRole::Owner->value,
        ));
        $managerResolver->upsert(new ManagerData(
            externalId: 501,
            name: 'Иван Петров',
            email: 'petrov@example.com',
            phone: '+7 999 111-11-11',
            role: ManagerRole::Owner->value,
        ));

        $regionResolver->upsert(new RegionData(externalId: 77, name: 'Москва'));
        $regionResolver->upsert(new RegionData(externalId: 77, name: 'Московский регион'));

        $tag = $tagResolver->resolve('лизинг');
        $sameTag = $tagResolver->resolve('лизинг');

        self::assertSame(1, Manager::query()->count());
        self::assertSame(1, Region::query()->count());
        self::assertSame(1, Tag::query()->count());

        self::assertDatabaseHas('managers', [
            'external_id' => 501,
            'name' => 'Иван Петров',
            'email' => 'petrov@example.com',
            'phone' => '+7 999 111-11-11',
            'role' => ManagerRole::Owner->value,
        ]);
        self::assertDatabaseHas('regions', [
            'external_id' => 77,
            'name' => 'Московский регион',
        ]);
        self::assertSame($manager->id, Manager::query()->where('external_id', 501)->value('id'));
        self::assertSame($tag->id, $sameTag->id);
    }
}
