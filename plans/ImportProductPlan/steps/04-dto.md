# Шаг 04 — DTO в `Domain/Catalog/Import/Data`

## Цель

Описать неизменяемые объекты-переносчики (DTO) для распарсенных узлов фида. Парсер (шаг 05)
строит DTO, резолверы и импортёр (шаги 06, 08) принимают типизированные данные вместо «сырых»
массивов/строк XML.

## Расположение

`app/Domain/Catalog/Import/Data`, неймспейс `App\Domain\Catalog\Import\Data`.
Можно использовать простые `readonly`-классы (PHP 8.3) либо `spatie/laravel-data`, если пакет добавлен.

## Состав DTO

### `CategoryData`
| Свойство | Тип | Источник XML |
|---|---|---|
| `externalId` | `int` | `category/@id` |
| `name` | `string` | `category/name` |
| `parentExternalId` | `?int` | `category/parent_id` |

### `StatusData` (универсальный для статусов верхнего уровня)
| Свойство | Тип | Источник |
|---|---|---|
| `externalId` | `int` | `status/@id` |
| `name` | `string` | `status/name` |
| `color` | `?string` | `status/color` |

### `ManagerData`
| Свойство | Тип | Источник (`manager/product_owner`) |
|---|---|---|
| `externalId` | `int` | `@id` |
| `name` | `string` | `name` |
| `email` | `?string` | `email` |
| `phone` | `?string` | `phone` |
| `role` | `?string` | `role` |

### `RegionData`
| Свойство | Тип | Источник |
|---|---|---|
| `externalId` | `int` | `region/@id` |
| `name` | `string` | `region/name` |

### `MediaItemData`
| Свойство | Тип | Источник (`media/media_item`) |
|---|---|---|
| `externalId` | `int` | `@id` |
| `fileName` | `string` | `file_name` |
| `fileUrl` | `string` | `file_url` |
| `mimeType` | `?string` | `mime_type` |
| `fileSize` | `?int` | `file_size` |
| `sortOrder` | `int` | `sort_order` |
| `isMain` | `bool` | `is_main_image` (1/0) |

### `StatusWithCommentData` (для check/loading/removal внутри объявления)
| Свойство | Тип | Источник |
|---|---|---|
| `name` | `string` | `*/status` (текст, без `@id`) |
| `comment` | `?string` | `*/comment` (CDATA) |

### `AdvertisementData` (агрегирующий DTO объявления)
| Свойство | Тип | Источник |
|---|---|---|
| `externalId` | `int` | `@id` |
| `name` | `string` | `title` |
| `sku` | `?string` | `sku` |
| `categoryExternalId` | `?int` | `category/@id` |
| `statusExternalId` | `?int` | `status/@id` |
| `stateExternalId` / `stateName` | `?int` / `?string` | `product_state/@id` + текст |
| `availabilityExternalId` / `availabilityName` | `?int` / `?string` | `product_available/@id` + текст |
| `price` | `?string` | `price/adv_price` |
| `showPrice` | `bool` | `price/show_price` |
| `priceComment` | `?string` | `price/adv_price_comment` |
| `productAddress` | `?string` | `location/product_address` |
| `publishedAt` | `?string` | `dates/published_at` |
| `manager` | `?ManagerData` | `manager/product_owner` |
| `regions` | `RegionData[]` | `location/regions/region` |
| `tags` | `string[]` | `tags/tag` |
| `media` | `MediaItemData[]` | `media/media_item` |
| `check` | `?StatusWithCommentData` | `check` |
| `loading` | `?StatusWithCommentData` | `loading` |
| `removal` | `?StatusWithCommentData` | `removal` |
| `mainCharacteristics` | `?string` | `main_characteristics` (CDATA) |
| `complectation` | `?string` | `complectation` |
| `technicalCharacteristics` | `?string` | `technical_characteristics` |
| `mainInfo` | `?string` | `main_info` |
| `additionalInfo` | `?string` | `additional_info` |

## Пример (readonly-класс)

```php
final readonly class MediaItemData
{
    public function __construct(
        public int $externalId,
        public string $fileName,
        public string $fileUrl,
        public ?string $mimeType,
        public ?int $fileSize,
        public int $sortOrder,
        public bool $isMain,
    ) {}
}
```

## Definition of Done

- [ ] Созданы DTO: `CategoryData`, `StatusData`, `ManagerData`, `RegionData`, `MediaItemData`, `StatusWithCommentData`, `AdvertisementData`.
- [ ] Все DTO — `readonly`, типизированные.
- [ ] Свойства соответствуют маппингу разделов 2.1–2.8 плана.
