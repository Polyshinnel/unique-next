# Шаг 06 — Резолверы справочников

## Цель

Идемпотентные upsert'ы справочников: вернуть существующую запись или создать/переименовать.
Единое правило (раздел 4 плана): **upsert по `external_id`**, при расхождении имени — переименовать.
Для справочников без `external_id` (теги, встроенные статусы) — match по `name`.

## Расположение

`app/Domain/Catalog/Import/Resolvers`, неймспейс `App\Domain\Catalog\Import\Resolvers`.

## Общий паттерн (по `external_id`)

```php
$row = Model::firstOrNew(['external_id' => $extId]);
if (! $row->exists) {
    $row->fill([...])->save();              // создать
} elseif ($row->name !== $name /* или др. поля */) {
    $row->update(['name' => $name, ...]);   // переименовать/обновить
}
return $row;
```

## `CategoryResolver`

- Маппит `CategoryData` → `categories` по `external_id`.
- **Два прохода:** сначала создать все категории без `parent_id`, затем во втором проходе
  проставить `parent_id` (резолв родителя по его `external_id`). Это снимает зависимость от
  порядка категорий в фиде.
- `slug` — генерируется при создании (транслит кириллицы; см. шаг 08 / раздел 10 плана),
  при rename имени `slug` **не менять**.
- API:
  - `upsertMany(iterable $categories): void` — первый проход (создание/rename);
  - `linkParents(iterable $categories): void` — второй проход (`parent_id`);
  - `resolveByExternalId(int $extId): ?Category`.

## `StatusResolvers`

Группа резолверов для статусов:

| Метод | Таблица | Источник | Ключ |
|---|---|---|---|
| `productStatus(StatusData)` | `product_statuses` | `advertisement_statuses` | `external_id` |
| `checkStatus(StatusData)` | `check_statuses` | `check_statuses` | `external_id` |
| `shipmentStatus(StatusData)` | `shipment_statuses` | `install_statuses` | `external_id` |
| `dismantleStatus(StatusData)` | `dismantle_statuses` | `install_statuses` | `external_id` |
| `equipmentState(int $extId, string $name)` | `equipment_states` | `product_state` | `external_id` |
| `equipmentAvailability(int $extId, string $name)` | `equipment_availabilities` | `product_available` | `external_id` |

- `install_statuses` пишется в **обе** таблицы (`shipment_statuses` и `dismantle_statuses`).
- `equipment_states`/`equipment_availabilities` наполняются по мере импорта объявлений
  (`firstOrCreate`/upsert по `external_id`, name из объявления).

### Встроенные статусы объявления (без `@id`)

Match по `name` среди уже импортированных справочников; если не найдено — создать **без** `external_id`:

| Метод | Таблица | Источник |
|---|---|---|
| `checkStatusByName(string $name)` | `check_statuses` | `check/status` |
| `shipmentStatusByName(string $name)` | `shipment_statuses` | `loading/status` |
| `dismantleStatusByName(string $name)` | `dismantle_statuses` | `removal/status` |

> Блоки верхнего уровня (`check_statuses`, `install_statuses`) импортируются ПЕРВЫМИ и проставляют
> `external_id` + `name`, что делает поиск по имени надёжным.

## `ManagerResolver`

- Upsert `managers` по `external_id` из `manager/product_owner`.
- Обновлять `name`/`email`/`phone`/`role` при расхождении.
- `has_telegram`/`has_whatsapp` — игнорировать (колонок нет; раздел 2.5).
- `creator` и `regional_representative` — **не** импортировать.
- API: `upsert(ManagerData): Manager`.

## `RegionResolver`

- Upsert `regions` по `external_id` из `location/regions/region`.
- Маппинг только `name` (без `city_name`, см. шаг 02).
- API: `upsert(RegionData): Region`.

## `TagResolver`

- `tags` без `external_id` → `firstOrCreate(['name' => $name])`.
- API: `resolve(string $name): Tag`.

## Definition of Done

- [ ] `CategoryResolver` с двумя проходами и генерацией slug.
- [ ] `StatusResolvers`: product/check/shipment/dismantle/state/availability + match-by-name.
- [ ] `install_statuses` пишется в обе таблицы.
- [ ] `ManagerResolver` (только owner), `RegionResolver` (без city_name), `TagResolver` (по name).
- [ ] Все резолверы идемпотентны: повторный вызов не дублирует и переименовывает при расхождении.
