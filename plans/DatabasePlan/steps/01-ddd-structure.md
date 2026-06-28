# Шаг 01 — Каркас DDD-структуры

## Цель

Создать директории доменов под архитектуру DDD. Правка `composer.json` **не требуется**
(PSR-4 `App\ => app/` уже настроен, см. `composer.json`).

## Действия

Создать следующее дерево директорий внутри `app/`:

```text
app/
├── Domain/
│   ├── Contact/
│   │   ├── Models/
│   │   ├── Data/
│   │   ├── Actions/
│   │   └── Repositories/
│   ├── Shipment/
│   │   ├── Models/
│   │   ├── Data/
│   │   ├── Actions/
│   │   └── Repositories/
│   ├── Banner/
│   │   ├── Models/
│   │   ├── Data/
│   │   └── Actions/
│   └── Catalog/
│       ├── Models/
│       ├── Data/
│       ├── Actions/
│       ├── Enums/
│       ├── Import/
│       └── Repositories/
└── Application/
```

> `app/Http/Controllers/` и `app/Models/User.php` остаются как есть.

## Команда (пример)

```bash
mkdir -p app/Domain/Contact/{Models,Data,Actions,Repositories} \
         app/Domain/Shipment/{Models,Data,Actions,Repositories} \
         app/Domain/Banner/{Models,Data,Actions} \
         app/Domain/Catalog/{Models,Data,Actions,Enums,Import,Repositories} \
         app/Application
```

> Git не хранит пустые директории. Чтобы каркас попал в репозиторий до появления классов,
> временно добавить `.gitkeep` в пустые папки (удалить, когда появятся реальные файлы).

## Неймспейсы

| Директория | Неймспейс |
|---|---|
| `app/Domain/Contact/Models` | `App\Domain\Contact\Models` |
| `app/Domain/Shipment/Models` | `App\Domain\Shipment\Models` |
| `app/Domain/Banner/Models` | `App\Domain\Banner\Models` |
| `app/Domain/Catalog/Models` | `App\Domain\Catalog\Models` |
| `app/Domain/Catalog/Import` | `App\Domain\Catalog\Import` |

## Проверка

- `composer dump-autoload` выполняется без ошибок.
- Тестовый класс в `App\Domain\Contact\Models` резолвится автозагрузчиком.

## Definition of Done

- [ ] Все директории созданы.
- [ ] Неймспейсы соответствуют PSR-4.
- [ ] `composer dump-autoload` проходит.
