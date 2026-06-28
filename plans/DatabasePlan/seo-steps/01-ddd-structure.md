# Шаг 01 — DDD-структура контекста `Seo`

## Цель

Создать каркас директорий для нового DDD-контекста `Seo`. Это отдельный контекст,
потому что речь о SEO **статических** страниц (а не записей каталога/отгрузок).

## Действия

Создать директории под неймспейс `App\Domain\Seo\...`:

```text
app/
└── Domain/
    └── Seo/
        ├── Models/         # PageSeo (шаг 03)
        ├── Data/           # DTO (PageSeoData) — опционально
        ├── Actions/        # UpsertPageSeoAction и т.п. — опционально
        └── Repositories/   # PageSeoRepository (lookup по key/path) — опционально
```

Команда:

```bash
mkdir -p app/Domain/Seo/{Models,Data,Actions,Repositories}
```

> `Data`, `Actions`, `Repositories` создаются как заготовки. Для минимальной реализации
> (миграция + модель + контроллер) достаточно `Models`. Остальное наполняется по мере надобности.

## Примечания

- PSR-4 `App\ => app/` уже настроен в `composer.json` — правки/перегенерация автозагрузчика не нужны.
- Неймспейс файлов: `App\Domain\Seo\Models`, `App\Domain\Seo\Data` и т.д.
- Контроллер API кладётся **не** в Domain, а в `app/Http/Controllers/Api` (см. шаг 05),
  по аналогии с существующим `App\Http\Controllers\Api\HealthController`.

## Definition of Done

- [ ] Директория `app/Domain/Seo` с подпапками `Models`/`Data`/`Actions`/`Repositories` создана.
- [ ] Структура согласуется с разделом 2 плана.
