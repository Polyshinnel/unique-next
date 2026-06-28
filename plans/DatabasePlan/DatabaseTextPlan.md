# DatabasePlan — структура БД для управления сайтом (Laravel 13 + MySQL 8.4, DDD)

## 1. Обзор

Документ описывает структуру базы данных для управления сайтом `uniqset2.com`: контакты,
отгрузки, баннеры главной страницы и каталог оборудования. Управление данными —
через Laravel 13 (PHP 8.3+), хранилище — MySQL 8.4 (`utf8mb4_unicode_ci`).
Архитектура — **DDD (Domain-Driven Design)** с разделением на ограниченные контексты
(bounded contexts).

Источник части данных каталога — XML-выгрузка объявлений (`advertisements_export`),
поэтому таблицы-справочники содержат поле `external_id` для идемпотентного импорта
(upsert по внешнему идентификатору).

### Ключевые сущности и контексты

| Контекст (Domain) | Назначение | Основные таблицы |
|---|---|---|
| `Contact` | Контактные данные сайта | `contacts` |
| `Shipment` | Отгрузки (события/новости об отгрузках) | `shipments`, `shipment_tags`, `shipment_tag`, `shipment_images` |
| `Banner` | Баннеры на главной странице | `banners` |
| `Catalog` | Каталог оборудования (товары + справочники) | `products` и связанные справочники/таблицы |

---

## 2. DDD-структура проекта

PSR-4 уже настроен как `App\ => app/` (см. `composer.json`), поэтому пространство имён
`App\Domain\...` подхватывается автоматически — **изменения в `composer.json` не нужны**.

```text
app/
├── Domain/
│   ├── Contact/
│   │   ├── Models/            # Eloquent-модели (Contact)
│   │   ├── Data/              # DTO (spatie/laravel-data или простые DTO)
│   │   ├── Actions/          # Сценарии (UpdateContactAction и т.п.)
│   │   └── Repositories/     # Доступ к данным (опционально)
│   ├── Shipment/
│   │   ├── Models/            # Shipment, ShipmentTag, ShipmentImage
│   │   ├── Data/
│   │   ├── Actions/
│   │   └── Repositories/
│   ├── Banner/
│   │   ├── Models/            # Banner
│   │   ├── Data/
│   │   └── Actions/
│   └── Catalog/
│       ├── Models/            # Product и все справочники/связанные модели
│       ├── Data/
│       ├── Actions/
│       ├── Enums/            # При необходимости (например, ManagerRole)
│       ├── Import/           # Импорт XML-выгрузки (parser + upsert)
│       └── Repositories/
├── Application/               # Прикладные сервисы / use-cases (по необходимости)
├── Http/                      # Controllers, Requests, Resources (тонкий слой)
│   └── Controllers/
└── Models/
    └── User.php               # Системная модель (оставляем как есть)
```

### Соглашения

- **Миграции**: единая директория `database/migrations`. Файлы именуются по таблицам,
  порядок (timestamp в имени) выстраивается так, чтобы родительские таблицы и справочники
  создавались до зависимых (FK).
- **Сидеры**: `database/seeders` — наполнение справочников (статусы, состояния, доступность).
- **Имена таблиц**: snake_case, множественное число (`products`, `banners`).
  Pivot-таблицы — в единственном числе, по алфавиту (`product_tag`, `shipment_tag`).
- **Кодировка**: `utf8mb4` / `utf8mb4_unicode_ci`.
- **Ключи**: PK `id` (`bigIncrements`), FK `*_id` (`foreignId`), внешний ключ импорта — `external_id` (`unsignedBigInteger`, индексируется).
- **Временные метки**: `created_at` / `updated_at` во всех контентных таблицах.
- **Мягкое удаление**: `deleted_at` (`softDeletes`) для `products`, `shipments`, `banners` (рекомендуется).
- **HTML-контент**: поля типа `text` (или `longText` для крупных) — хранят сырой HTML.
- **Цена**: `decimal(10, 2)`.

---

## 3. Контекст `Contact` — контакты сайта

Одна строка-настройка (singleton). Допускается несколько записей, если потребуется
несколько контактных блоков; тогда добавляется `is_active` / `sort_order`.

### Таблица `contacts`

| Поле | Тип | Null | Описание |
|---|---|---|---|
| `id` | bigint UNSIGNED PK | нет | Идентификатор |
| `address` | varchar(255) | да | Адрес |
| `phone` | varchar(50) | да | Телефон |
| `email` | varchar(255) | да | Почта |
| `work_schedule` | varchar(255) | да | График работы |
| `latitude` | decimal(10,7) | да | Координата (широта) |
| `longitude` | decimal(10,7) | да | Координата (долгота) |
| `telegram` | varchar(255) | да | Ссылка/хэндл Telegram |
| `vk` | varchar(255) | да | Ссылка ВК |
| `max` | varchar(255) | да | Ссылка/хэндл MAX |
| `inn` | varchar(20) | да | ИНН |
| `ogrn` | varchar(20) | да | ОГРН |
| `created_at` / `updated_at` | timestamp | да | Системные метки |

> Координаты разнесены на `latitude` / `longitude` для удобства карт. Если нужна строка
> «как есть», можно дополнительно завести `coordinates` varchar(255).

---

## 4. Контекст `Shipment` — отгрузки

### Таблица `shipments`

| Поле | Тип | Null | Описание |
|---|---|---|---|
| `id` | bigint UNSIGNED PK | нет | Идентификатор |
| `shipment_date` | date | нет | Дата отгрузки |
| `location` | varchar(255) | да | Локация отгрузки |
| `title` | varchar(255) | нет | Название отгрузки |
| `short_description` | text | да | Краткое описание |
| `sort_order` | int UNSIGNED | нет (default 0) | Порядок вывода |
| `is_active` | boolean | нет (default true) | Признак публикации |
| `created_at` / `updated_at` | timestamp | да | Системные метки |
| `deleted_at` | timestamp | да | Мягкое удаление |

### Таблица `shipment_tags` (отдельная таблица тегов отгрузок)

| Поле | Тип | Null | Описание |
|---|---|---|---|
| `id` | bigint UNSIGNED PK | нет | Идентификатор |
| `name` | varchar(255) | нет | Название тега (уникальное) |
| `created_at` / `updated_at` | timestamp | да | Системные метки |

### Таблица `shipment_tag` (pivot many-to-many)

| Поле | Тип | Null | Описание |
|---|---|---|---|
| `id` | bigint UNSIGNED PK | нет | Идентификатор |
| `shipment_id` | FK → `shipments.id` | нет | Отгрузка (onDelete cascade) |
| `shipment_tag_id` | FK → `shipment_tags.id` | нет | Тег (onDelete cascade) |

> Уникальный индекс `(shipment_id, shipment_tag_id)`.

### Таблица `shipment_images`

| Поле | Тип | Null | Описание |
|---|---|---|---|
| `id` | bigint UNSIGNED PK | нет | Идентификатор |
| `shipment_id` | FK → `shipments.id` | нет | Отгрузка (onDelete cascade) |
| `file_name` | varchar(255) | нет | Имя файла |
| `file_path` | varchar(512) | нет | Путь к файлу |
| `is_main` | boolean | нет (default false) | Главное изображение отгрузки |
| `sort_order` | int UNSIGNED | нет (default 0) | Порядок |
| `created_at` / `updated_at` | timestamp | да | Системные метки |

> Для гарантии единственного главного изображения — частичная проверка на уровне
> приложения (Action) либо триггер/уникальный индекс по `(shipment_id)` где `is_main = 1`.

---

## 5. Контекст `Banner` — баннеры главной страницы

### Таблица `banners`

| Поле | Тип | Null | Описание |
|---|---|---|---|
| `id` | bigint UNSIGNED PK | нет | Идентификатор |
| `image` | varchar(512) | да | Путь к изображению |
| `title` | varchar(255) | да | Заголовок |
| `text` | text | да | Текст баннера |
| `button_one_text` | varchar(255) | да | Текст кнопки 1 |
| `button_one_url` | varchar(512) | да | Ссылка кнопки 1 |
| `button_two_text` | varchar(255) | да | Текст кнопки 2 |
| `button_two_url` | varchar(512) | да | Ссылка кнопки 2 |
| `sort_order` | int UNSIGNED | нет (default 0) | Порядок |
| `is_active` | boolean | нет (default true) | Признак публикации |
| `created_at` / `updated_at` | timestamp | да | Системные метки |
| `deleted_at` | timestamp | да | Мягкое удаление |

---

## 6. Контекст `Catalog` — каталог оборудования

Самый крупный контекст. Делится на: **справочники**, **товар** (`products`),
**контентные блоки товара**, **статусные блоки товара**, **изображения и теги товара**.

### 6.1 Справочники

#### `regions` — регионы

| Поле | Тип | Null | Описание |
|---|---|---|---|
| `id` | bigint UNSIGNED PK | нет | Идентификатор |
| `external_id` | bigint UNSIGNED | да | ID из выгрузки (`region id`) |
| `name` | varchar(255) | нет | Название региона |
| `city_name` | varchar(255) | да | Город (из выгрузки `city_name`) |
| `created_at` / `updated_at` | timestamp | да | Системные метки |

> Индекс `external_id` (unique). `city_name` добавлено по XML (расширение исходных наметок).

#### `categories` — категории

| Поле | Тип | Null | Описание |
|---|---|---|---|
| `id` | bigint UNSIGNED PK | нет | Идентификатор |
| `external_id` | bigint UNSIGNED | да | ID из выгрузки (`category id`) |
| `name` | varchar(255) | нет | Название |
| `slug` | varchar(255) | нет | Ссылка (URL slug, unique) |
| `parent_id` | FK → `categories.id` | да | Родительская категория (self-ref, onDelete set null) |
| `title` | varchar(255) | да | SEO title |
| `description` | text | да | SEO description |
| `og_image` | varchar(512) | да | OG-изображение |
| `created_at` / `updated_at` | timestamp | да | Системные метки |

> Индексы: `external_id` (unique), `slug` (unique), `parent_id`.

#### `equipment_availabilities` — доступность оборудования

| Поле | Тип | Null | Описание |
|---|---|---|---|
| `id` | bigint UNSIGNED PK | нет | Идентификатор |
| `external_id` | bigint UNSIGNED | да | ID из выгрузки (`product_available`) |
| `name` | varchar(255) | нет | Название (например, «В наличии») |
| `color` | varchar(7) | да | HEX-цвет (`#RRGGBB`) |
| `created_at` / `updated_at` | timestamp | да | Системные метки |

#### `equipment_states` — состояние оборудования

| Поле | Тип | Null | Описание |
|---|---|---|---|
| `id` | bigint UNSIGNED PK | нет | Идентификатор |
| `external_id` | bigint UNSIGNED | да | ID из выгрузки (`product_state`) |
| `name` | varchar(255) | нет | Состояние: «Новое», «Б.У», «Вост/Модерн» |
| `created_at` / `updated_at` | timestamp | да | Системные метки |

#### `check_statuses` — статусы проверки

| Поле | Тип | Null | Описание |
|---|---|---|---|
| `id` | bigint UNSIGNED PK | нет | Идентификатор |
| `external_id` | bigint UNSIGNED | да | ID из выгрузки (`check_statuses`) |
| `name` | varchar(255) | нет | Название (например, «С проверкой») |
| `color` | varchar(7) | да | HEX-цвет |
| `created_at` / `updated_at` | timestamp | да | Системные метки |

#### `dismantle_statuses` — статусы демонтажа

| Поле | Тип | Null | Описание |
|---|---|---|---|
| `id` | bigint UNSIGNED PK | нет | Идентификатор |
| `external_id` | bigint UNSIGNED | да | ID из выгрузки |
| `name` | varchar(255) | нет | Название (например, «Поставщиком») |
| `created_at` / `updated_at` | timestamp | да | Системные метки |

#### `shipment_statuses` — статусы отгрузки (погрузки) товара

| Поле | Тип | Null | Описание |
|---|---|---|---|
| `id` | bigint UNSIGNED PK | нет | Идентификатор |
| `external_id` | bigint UNSIGNED | да | ID из выгрузки |
| `name` | varchar(255) | нет | Название (например, «Поставщиком») |
| `created_at` / `updated_at` | timestamp | да | Системные метки |

> В XML загрузка (`loading`) и демонтаж (`removal`) ссылаются на словарь `install_statuses`
> (значения «Поставщиком», «Клиентом» и т.д.). В нашей модели это два независимых справочника
> (`shipment_statuses` и `dismantle_statuses`) согласно наметкам; при импорте оба наполняются
> из `install_statuses`. Маппинг описан в разделе 8.

#### `tags` — теги товара

| Поле | Тип | Null | Описание |
|---|---|---|---|
| `id` | bigint UNSIGNED PK | нет | Идентификатор |
| `name` | varchar(255) | нет | Название (unique) |
| `created_at` / `updated_at` | timestamp | да | Системные метки |

#### `managers` — менеджеры

| Поле | Тип | Null | Описание |
|---|---|---|---|
| `id` | bigint UNSIGNED PK | нет | Идентификатор |
| `external_id` | bigint UNSIGNED | да | ID из выгрузки (`creator/owner/representative id`) |
| `name` | varchar(255) | нет | Имя менеджера |
| `phone` | varchar(50) | да | Телефон |
| `email` | varchar(255) | да | Почта |
| `vk` | varchar(255) | да | ВК |
| `max` | varchar(255) | да | MAX |
| `telegram` | varchar(255) | да | Telegram |
| `role` | varchar(100) | да | Роль (из выгрузки `role`) |
| `created_at` / `updated_at` | timestamp | да | Системные метки |

> `role` добавлено по XML. Поля `has_whatsapp` / `has_telegram` из выгрузки — опциональны
> (можно добавить `boolean` при необходимости).

#### `product_statuses` — статусы товара

| Поле | Тип | Null | Описание |
|---|---|---|---|
| `id` | bigint UNSIGNED PK | нет | Идентификатор |
| `external_id` | bigint UNSIGNED | да | ID из выгрузки (`advertisement_statuses`) |
| `name` | varchar(255) | нет | «В продаже», «Резерв», «Ревизия», «Холд», «Продано», «Архив» |
| `color` | varchar(7) | да | HEX-цвет |
| `created_at` / `updated_at` | timestamp | да | Системные метки |

### 6.2 Товар

#### `products` — товары

| Поле | Тип | Null | Описание |
|---|---|---|---|
| `id` | bigint UNSIGNED PK | нет | Идентификатор |
| `external_id` | bigint UNSIGNED | да | ID объявления из выгрузки (`advertisement id` / `product_id`) |
| `name` | varchar(255) | нет | Название товара (XML `title`) |
| `sku` | varchar(100) | да | Артикул (unique) |
| `description` | text | да | Описание |
| `og_image` | varchar(512) | да | OG-изображение |
| `category_id` | FK → `categories.id` | да | Категория (onDelete set null) |
| `manager_id` | FK → `managers.id` | да | Менеджер-владелец (onDelete set null) |
| `equipment_state_id` | FK → `equipment_states.id` | да | Состояние оборудования |
| `equipment_availability_id` | FK → `equipment_availabilities.id` | да | Доступность оборудования |
| `product_status_id` | FK → `product_statuses.id` | да | Статус товара |
| `price` | decimal(10,2) | да | Цена |
| `show_price` | boolean | нет (default true) | Отображать цену |
| `price_comment` | text | да | Комментарий по цене |
| `product_address` | varchar(255) | да | Адрес товара (XML `product_address`) |
| `published_at` | timestamp | да | Дата публикации (XML `published_at`) |
| `created_at` / `updated_at` | timestamp | да | Системные метки |
| `deleted_at` | timestamp | да | Мягкое удаление |

> Расширения относительно исходных наметок (по данным XML, рекомендуется):
> `category_id`, `product_address`, `published_at`. Связь товара с регионами вынесена в
> pivot `product_region` (в XML `regions` — массив). Связь с несколькими менеджерами
> (creator / owner / regional_representative) — опциональный pivot `product_manager` (см. 6.5).

### 6.3 Контентные блоки товара (HTML)

Все таблицы имеют отношение **1:1** к товару (`product_id` — unique) и хранят сырой HTML
в поле `content`. Вынесены в отдельные таблицы согласно наметкам.

| Таблица | Назначение | XML-источник |
|---|---|---|
| `product_main_characteristics` | Основные характеристики | `main_characteristics` |
| `product_complectations` | Комплектация | `complectation` |
| `product_technical_characteristics` | Технические характеристики | `technical_characteristics` |
| `product_main_infos` | Главная информация | `main_info` |
| `product_additional_infos` | Дополнительная информация | `additional_info` |

Структура каждой из таблиц:

| Поле | Тип | Null | Описание |
|---|---|---|---|
| `id` | bigint UNSIGNED PK | нет | Идентификатор |
| `product_id` | FK → `products.id` | нет | Товар (unique, onDelete cascade) |
| `content` | longText | да | Сырой HTML |
| `created_at` / `updated_at` | timestamp | да | Системные метки |

### 6.4 Статусные блоки товара

Отношение **1:1** к товару (`product_id` — unique), плюс ссылка на статус и HTML-комментарий.

#### `product_checks` — проверка товара (XML `check`)

| Поле | Тип | Null | Описание |
|---|---|---|---|
| `id` | bigint UNSIGNED PK | нет | Идентификатор |
| `product_id` | FK → `products.id` | нет | Товар (unique, onDelete cascade) |
| `check_status_id` | FK → `check_statuses.id` | да | Статус проверки |
| `comment` | longText | да | Сырой HTML-комментарий |
| `created_at` / `updated_at` | timestamp | да | Системные метки |

#### `product_loadings` — погрузка товара (XML `loading`)

| Поле | Тип | Null | Описание |
|---|---|---|---|
| `id` | bigint UNSIGNED PK | нет | Идентификатор |
| `product_id` | FK → `products.id` | нет | Товар (unique, onDelete cascade) |
| `shipment_status_id` | FK → `shipment_statuses.id` | да | Статус отгрузки/погрузки |
| `comment` | longText | да | Сырой HTML-комментарий |
| `created_at` / `updated_at` | timestamp | да | Системные метки |

#### `product_dismantlings` — демонтаж товара (XML `removal`)

| Поле | Тип | Null | Описание |
|---|---|---|---|
| `id` | bigint UNSIGNED PK | нет | Идентификатор |
| `product_id` | FK → `products.id` | нет | Товар (unique, onDelete cascade) |
| `dismantle_status_id` | FK → `dismantle_statuses.id` | да | Статус демонтажа |
| `comment` | longText | да | Сырой HTML-комментарий |
| `created_at` / `updated_at` | timestamp | да | Системные метки |

### 6.5 Изображения и связи товара

#### `product_images` — изображения товара (XML `media/media_item`)

| Поле | Тип | Null | Описание |
|---|---|---|---|
| `id` | bigint UNSIGNED PK | нет | Идентификатор |
| `external_id` | bigint UNSIGNED | да | ID из выгрузки (`media_item id`) |
| `product_id` | FK → `products.id` | нет | Товар (onDelete cascade) |
| `file_name` | varchar(255) | нет | Имя файла |
| `file_path` | varchar(512) | нет | Путь к файлу |
| `file_url` | varchar(512) | да | Полный URL (XML `file_url`) |
| `mime_type` | varchar(100) | да | MIME-тип |
| `file_size` | int UNSIGNED | да | Размер файла, байт |
| `is_main` | boolean | нет (default false) | Главное изображение (XML `is_main_image`) |
| `sort_order` | int UNSIGNED | нет (default 0) | Порядок |
| `created_at` / `updated_at` | timestamp | да | Системные метки |

> Минимум по наметкам: `product_id`, `file_name`, `file_path`, `sort_order`. Добавлены
> `is_main` (требование «пометка главного изображения») и поля из XML (`external_id`,
> `file_url`, `mime_type`, `file_size`).

#### `product_tag` — связь товара и тегов (pivot)

| Поле | Тип | Null | Описание |
|---|---|---|---|
| `id` | bigint UNSIGNED PK | нет | Идентификатор |
| `product_id` | FK → `products.id` | нет | Товар (onDelete cascade) |
| `tag_id` | FK → `tags.id` | нет | Тег (onDelete cascade) |

> Уникальный индекс `(product_id, tag_id)`.

#### `product_region` — связь товара и регионов (pivot, расширение по XML)

| Поле | Тип | Null | Описание |
|---|---|---|---|
| `id` | bigint UNSIGNED PK | нет | Идентификатор |
| `product_id` | FK → `products.id` | нет | Товар (onDelete cascade) |
| `region_id` | FK → `regions.id` | нет | Регион (onDelete cascade) |

> Уникальный индекс `(product_id, region_id)`. Нужен, т.к. в XML `regions` — массив.

#### `product_manager` — связь товара и менеджеров с ролью (опционально, по XML)

| Поле | Тип | Null | Описание |
|---|---|---|---|
| `id` | bigint UNSIGNED PK | нет | Идентификатор |
| `product_id` | FK → `products.id` | нет | Товар (onDelete cascade) |
| `manager_id` | FK → `managers.id` | нет | Менеджер (onDelete cascade) |
| `role` | varchar(50) | нет | Роль: `creator` / `owner` / `regional_representative` |

> Уникальный индекс `(product_id, manager_id, role)`. Если достаточно одного менеджера на
> товар (как в исходных наметках), эту таблицу можно не вводить, оставив только
> `products.manager_id`. Рекомендуется ввести для полной поддержки импорта.

---

## 7. Схема взаимодействия (ER, текстом)

```text
contacts                      (изолированная настройка)

banners                       (изолированная сущность)

shipments 1───* shipment_images
shipments *───* shipment_tags        (через shipment_tag)

categories 1───* categories          (self-ref: parent_id)
categories 1───* products

regions    *───* products            (через product_region)
managers   1───* products            (manager_id; опц. *──* через product_manager)
equipment_states          1───* products
equipment_availabilities  1───* products
product_statuses          1───* products

products 1───1 product_main_characteristics
products 1───1 product_complectations
products 1───1 product_technical_characteristics
products 1───1 product_main_infos
products 1───1 product_additional_infos

products 1───1 product_checks        *───1 check_statuses
products 1───1 product_loadings      *───1 shipment_statuses
products 1───1 product_dismantlings  *───1 dismantle_statuses

products 1───* product_images        (is_main помечает главное)
products *───* tags                  (через product_tag)
```

### Сводка связей по `products`

| Связь | Тип | Через |
|---|---|---|
| Категория | many-to-one | `products.category_id` |
| Регионы | many-to-many | `product_region` |
| Менеджер(ы) | many-to-one / many-to-many | `products.manager_id` / `product_manager` |
| Состояние, доступность, статус | many-to-one | FK на справочники |
| Контентные блоки (5 шт.) | one-to-one | `product_*` таблицы |
| Проверка / погрузка / демонтаж | one-to-one | `product_checks/loadings/dismantlings` |
| Изображения | one-to-many | `product_images` |
| Теги | many-to-many | `product_tag` |

---

## 8. Маппинг XML-выгрузки → таблицы (импорт)

| XML-узел | Таблица / поле | Ключ upsert |
|---|---|---|
| `<categories>/<category>` | `categories` (`name`, `parent_id`) | `external_id` ← `category id` |
| `<advertisement_statuses>/<status>` | `product_statuses` (`name`, `color`) | `external_id` ← `status id` |
| `<check_statuses>/<status>` | `check_statuses` (`name`, `color`) | `external_id` ← `status id` |
| `<install_statuses>/<status>` | `shipment_statuses` **и** `dismantle_statuses` (`name`) | `external_id` ← `status id` |
| `<advertisement>` | `products` | `external_id` ← `advertisement id` |
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
| `product_state` | `products.equipment_state_id` | по `equipment_states.external_id` |
| `product_available` | `products.equipment_availability_id` | по `equipment_availabilities.external_id` |
| `location/regions/region` | `regions` + `product_region` | `regions.external_id` ← `region id` |
| `location/product_address` | `products.product_address` | — |
| `manager/creator` | `managers` + `product_manager(role=creator)` | `managers.external_id` |
| `manager/product_owner` | `managers` + `products.manager_id` (owner) | `managers.external_id` |
| `manager/regional_representative` | `managers` + `product_manager(role=regional_representative)` | `managers.external_id` |
| `media/media_item` | `product_images` | `external_id` ← `media_item id` |
| `media_item/is_main_image` | `product_images.is_main` | — |
| `tags/tag` | `tags` + `product_tag` | `tags.name` |
| `dates/published_at` | `products.published_at` | — |

> Импорт реализуется в `app/Domain/Catalog/Import` (парсер XML + idempotent upsert через
> `external_id`). Справочники `equipment_states` и `equipment_availabilities` в выгрузке
> приходят как атрибуты товара (`product_state id`, `product_available id`) — наполняются
> по мере импорта (firstOrCreate по `external_id`).

---

## 9. Порядок создания миграций

Сначала независимые справочники, затем `products`, затем зависимые от товара таблицы и pivot'ы.

1. `contacts`
2. `banners`
3. `shipments`, `shipment_tags`, `shipment_tag`, `shipment_images`
4. Справочники каталога: `regions`, `categories`, `equipment_availabilities`,
   `equipment_states`, `check_statuses`, `dismantle_statuses`, `shipment_statuses`,
   `tags`, `managers`, `product_statuses`
5. `products`
6. Контентные блоки: `product_main_characteristics`, `product_complectations`,
   `product_technical_characteristics`, `product_main_infos`, `product_additional_infos`
7. Статусные блоки: `product_checks`, `product_loadings`, `product_dismantlings`
8. `product_images`
9. Pivot'ы: `product_tag`, `product_region`, `product_manager`

### Сидеры (справочники по умолчанию)

- `equipment_states`: «Новое», «Б.У», «Вост/Модерн»
- `product_statuses`: «Ревизия», «В продаже», «Резерв», «Холд», «Продано», «Архив» (+ color)
- `check_statuses`: «С проверкой», «Без проверки», «Возможно подключение» (+ color)
- `dismantle_statuses` / `shipment_statuses`: «Поставщиком», «Поставщиком (за доп.плату)», «Клиентом», «Другое»

---

## 10. Чеклист реализации

- [ ] Создать DDD-структуру `app/Domain/{Contact,Shipment,Banner,Catalog}`
- [ ] Миграции по порядку из раздела 9
- [ ] Eloquent-модели + связи (`hasOne`, `hasMany`, `belongsTo`, `belongsToMany`)
- [ ] Сидеры справочников (раздел 9)
- [ ] Импорт XML-выгрузки (`Catalog/Import`) с upsert по `external_id`
- [ ] Индексы: `external_id` (unique) в справочниках, `slug`/`sku` (unique), уникальные индексы pivot'ов
- [ ] Тесты: миграции применяются, импорт идемпотентен (повторный прогон не дублирует данные)
