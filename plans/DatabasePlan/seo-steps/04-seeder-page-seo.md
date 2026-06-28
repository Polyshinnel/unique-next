# Шаг 04 — Сидер `PageSeoSeeder`

## Цель

Наполнить `page_seo` 14 статическими страницами. Идемпотентно — `updateOrCreate` по `key`.

## Действия

Сгенерировать сидер:

```bash
php artisan make:seeder PageSeoSeeder
```

Файл: `database/seeders/PageSeoSeeder.php`, неймспейс `Database\Seeders`.

## Список страниц

URL-пути соответствуют существующим маршрутам Next.js (`resources/js/app/**/page.tsx`).

| `key` | `path` | `name` |
|---|---|---|
| `home` | `/` | Главная |
| `services` | `/services` | Услуги |
| `catalog` | `/catalog` | Каталог оборудования |
| `shipments` | `/otgruzki` | Отгрузки |
| `company` | `/about` | Компания |
| `contacts` | `/contacts` | Контакты |
| `why_we` | `/why-we` | Почему мы |
| `vacancy` | `/vacancy` | Вакансии |
| `privacy_policy` | `/private-policy` | Политика конфиденциальности |
| `labor_safety` | `/ohrana-truda` | Охрана труда |
| `equipment_sale` | `/services/prodazha-oborudovaniya` | Продажа оборудования |
| `equipment_buyout` | `/services/vykup` | Выкуп оборудования |
| `tools_sale` | `/services/prodazha-instrumenta` | Продажа инструмента |
| `equipment_import` | `/services/import-oborudovaniya` | Импорт оборудования |

> При желании в каждую запись можно сразу положить `title`/`description`, перенеся текущие
> статические значения `metadata` со страниц (см. шаг 07). Это нужно сделать **до** перевода
> страниц на `generateMetadata`, чтобы фронтенд получал готовые значения, а не дефолты.

## Реализация

```php
public function run(): void
{
    $pages = [
        ['key' => 'home', 'path' => '/', 'name' => 'Главная'],
        ['key' => 'services', 'path' => '/services', 'name' => 'Услуги'],
        ['key' => 'catalog', 'path' => '/catalog', 'name' => 'Каталог оборудования'],
        ['key' => 'shipments', 'path' => '/otgruzki', 'name' => 'Отгрузки'],
        ['key' => 'company', 'path' => '/about', 'name' => 'Компания'],
        ['key' => 'contacts', 'path' => '/contacts', 'name' => 'Контакты'],
        ['key' => 'why_we', 'path' => '/why-we', 'name' => 'Почему мы'],
        ['key' => 'vacancy', 'path' => '/vacancy', 'name' => 'Вакансии'],
        ['key' => 'privacy_policy', 'path' => '/private-policy', 'name' => 'Политика конфиденциальности'],
        ['key' => 'labor_safety', 'path' => '/ohrana-truda', 'name' => 'Охрана труда'],
        ['key' => 'equipment_sale', 'path' => '/services/prodazha-oborudovaniya', 'name' => 'Продажа оборудования'],
        ['key' => 'equipment_buyout', 'path' => '/services/vykup', 'name' => 'Выкуп оборудования'],
        ['key' => 'tools_sale', 'path' => '/services/prodazha-instrumenta', 'name' => 'Продажа инструмента'],
        ['key' => 'equipment_import', 'path' => '/services/import-oborudovaniya', 'name' => 'Импорт оборудования'],
    ];

    foreach ($pages as $page) {
        PageSeo::updateOrCreate(['key' => $page['key']], $page);
    }
}
```

Не забыть `use App\Domain\Seo\Models\PageSeo;` вверху файла.

Зарегистрировать вызов в `Database\Seeders\DatabaseSeeder::run()`:

```php
$this->call([
    PageSeoSeeder::class,
]);
```

## Замечания

- `path` в сидере хранить в нормализованном виде (ведущий слэш, без trailing-слэша, кроме `/`),
  чтобы совпадало с нормализацией в контроллере (шаг 05).
- `updateOrCreate` по `key` делает повторный прогон безопасным и позволяет дополнять список новыми страницами.

## Проверка

```bash
php artisan db:seed --class=PageSeoSeeder
```

Повторный запуск не создаёт дубликатов (14 строк остаются 14).

## Definition of Done

- [ ] `PageSeoSeeder` создаёт все 14 страниц.
- [ ] Используется `updateOrCreate` по `key` (идемпотентность).
- [ ] Зарегистрирован в `DatabaseSeeder`.
- [ ] `path` нормализованы.
