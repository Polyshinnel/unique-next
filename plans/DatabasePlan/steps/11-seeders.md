# Шаг 11 — Сидеры справочников

## Цель

Наполнить справочники значениями по умолчанию (раздел 9 плана). Сидеры в `database/seeders`,
неймспейс `Database\Seeders`. Использовать `firstOrCreate`/`updateOrCreate` по `name` —
идемпотентность (повторный прогон не дублирует).

## Данные по умолчанию

### `equipment_states`
- «Новое», «Б.У», «Вост/Модерн»

### `product_statuses` (+ `color`)
- «Ревизия», «В продаже», «Резерв», «Холд», «Продано», «Архив»
- Подобрать HEX-цвета (например: в продаже — зелёный, резерв — жёлтый, продано — серый).

### `check_statuses` (+ `color`)
- «С проверкой», «Без проверки», «Возможно подключение»

### `dismantle_statuses` и `shipment_statuses`
- «Поставщиком», «Поставщиком (за доп.плату)», «Клиентом», «Другое»
- Наполняются **оба** справочника (значения из XML `install_statuses`).

## Реализация

- Отдельные классы: `EquipmentStateSeeder`, `ProductStatusSeeder`, `CheckStatusSeeder`,
  `DismantleStatusSeeder`, `ShipmentStatusSeeder`.
- Зарегистрировать их вызовы в `Database\Seeders\DatabaseSeeder::run()` через `$this->call([...])`.

```php
foreach (['Новое', 'Б.У', 'Вост/Модерн'] as $name) {
    EquipmentState::firstOrCreate(['name' => $name]);
}
```

> `regions`, `categories`, `tags`, `managers`, `equipment_availabilities` наполняются
> импортом XML (шаг 12), а не сидерами.

## Проверка

- `php artisan db:seed` отрабатывает без ошибок.
- Повторный `php artisan db:seed` не создаёт дубликатов.

## Definition of Done

- [ ] Сидеры для `equipment_states`, `product_statuses`, `check_statuses`,
      `dismantle_statuses`, `shipment_statuses`.
- [ ] Зарегистрированы в `DatabaseSeeder`.
- [ ] Идемпотентны (firstOrCreate по `name`).
