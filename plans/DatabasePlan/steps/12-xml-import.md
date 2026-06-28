# Шаг 12 — Импорт XML-выгрузки

## Цель

Идемпотентный импорт `advertisements_export` в `app/Domain/Catalog/Import`: парсер XML +
upsert по `external_id`. Повторный прогон не дублирует данные.

## Компоненты (неймспейс `App\Domain\Catalog\Import`)

- **`XmlReader`/Parser** — потоковое чтение XML (`XMLReader` для больших файлов).
- **`ImportCatalogAction`** (или несколько Action) — оркестрация upsert'ов в транзакции.
- **Artisan-команда** `catalog:import {path}` — точка входа (`app/Console/Commands`).

## Порядок импорта (от справочников к товару)

1. Справочники: `categories`, `product_statuses` (`advertisement_statuses`),
   `check_statuses`, `install_statuses` → `shipment_statuses` **и** `dismantle_statuses`.
2. `products` (upsert по `external_id` = `advertisement id`).
3. Зависимости товара: контентные блоки, статусные блоки, изображения, теги, регионы, менеджеры.

## Маппинг XML → таблицы (раздел 8 плана)

| XML-узел | Таблица / поле | Ключ upsert |
|---|---|---|
| `categories/category` | `categories` (`name`, `parent_id`) | `external_id` ← `category id` |
| `advertisement_statuses/status` | `product_statuses` (`name`, `color`) | `external_id` ← `status id` |
| `check_statuses/status` | `check_statuses` (`name`, `color`) | `external_id` ← `status id` |
| `install_statuses/status` | `shipment_statuses` **и** `dismantle_statuses` (`name`) | `external_id` ← `status id` |
| `advertisement` | `products` | `external_id` ← `advertisement id` |
| `advertisement/title` | `products.name` | — |
| `advertisement/sku` | `products.sku` | — |
| `advertisement/category` | `products.category_id` | по `categories.external_id` |
| `advertisement/status` | `products.product_status_id` | по `product_statuses.external_id` |
| `main_characteristics` | `product_main_characteristics.content` | по `product_id` |
| `complectation` | `product_complectations.content` | по `product_id` |
| `technical_characteristics` | `product_technical_characteristics.content` | по `product_id` |
| `main_info` | `product_main_infos.content` | по `product_id` |
| `additional_info` | `product_additional_infos.content` | по `product_id` |
| `check/status` + `check/comment` | `product_checks` (`check_status_id`, `comment`) | по `product_id` |
| `loading/status` + `loading/comment` | `product_loadings` (`shipment_status_id`, `comment`) | по `product_id` |
| `removal/status` + `removal/comment` | `product_dismantlings` (`dismantle_status_id`, `comment`) | по `product_id` |
| `price/adv_price` | `products.price` | — |
| `price/adv_price_comment` | `products.price_comment` | — |
| `price/show_price` | `products.show_price` (1/0 → bool) | — |
| `product_state` | `products.equipment_state_id` | по `equipment_states.external_id` (firstOrCreate) |
| `product_available` | `products.equipment_availability_id` | по `equipment_availabilities.external_id` (firstOrCreate) |
| `location/regions/region` | `regions` + `product_region` | `regions.external_id` ← `region id` |
| `location/product_address` | `products.product_address` | — |
| `manager/creator` | `managers` + `product_manager(role=creator)` | `managers.external_id` |
| `manager/product_owner` | `managers` + `products.manager_id` (owner) | `managers.external_id` |
| `manager/regional_representative` | `managers` + `product_manager(role=regional_representative)` | `managers.external_id` |
| `media/media_item` | `product_images` | `external_id` ← `media_item id` |
| `media_item/is_main_image` | `product_images.is_main` | — |
| `tags/tag` | `tags` + `product_tag` | `tags.name` |
| `dates/published_at` | `products.published_at` | — |

## Правила идемпотентности

- Все upsert'ы — через `updateOrCreate(['external_id' => ...], [...])` (или по `name`/`slug`/`sku`).
- `equipment_states` / `equipment_availabilities` наполняются по мере импорта (`firstOrCreate` по `external_id`).
- `install_statuses` записывается в оба справочника (`shipment_statuses` и `dismantle_statuses`).
- Связи (`product_region`, `product_tag`, `product_manager`) синхронизировать через `sync`/`syncWithoutDetaching`.
- Импорт каждого товара — в транзакции; логировать пропуски/ошибки.

## Definition of Done

- [ ] Парсер XML и `ImportCatalogAction` в `Catalog/Import`.
- [ ] Artisan-команда `catalog:import`.
- [ ] Все узлы из маппинга обрабатываются.
- [ ] Повторный прогон не дублирует данные (проверяется в шаге 13).
