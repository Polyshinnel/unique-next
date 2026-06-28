# Шаг 11 — Планировщик

## Цель

Запускать импорт автоматически по расписанию, без параллельных и дублирующих прогонов.

## Расписание в `routes/console.php`

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('catalog:import-products')
    ->hourly()                 // частоту согласовать (фид обновляется ~раз в сутки)
    ->withoutOverlapping()
    ->onOneServer()
    ->runInBackground();
```

- `withoutOverlapping()` — не запускать новый прогон, пока идёт предыдущий.
- `onOneServer()` — на кластере выполняет только один сервер (требует общего кэш-lock).
- `runInBackground()` — не блокировать остальные задачи планировщика.
- Частоту (`->hourly()` / `->daily()` / `->cron(...)`) согласовать с частотой обновления фида.

## Запуск планировщика в проде

Планировщик гоняется воркером или системным cron:

```bash
# системный cron (раз в минуту дергает планировщик Laravel)
* * * * * cd /app && php artisan schedule:run >> /dev/null 2>&1

# либо долгоживущий процесс
php artisan schedule:work
```

- Проверить `docker/supervisor/supervisord.conf`: должен быть процесс для
  `schedule:work` (или cron) **и** для `queue:work`/Horizon (job из шага 09 выполняется воркером).
- Очередь `imports` должна обслуживаться воркером: `php artisan queue:work --queue=imports`
  или соответствующая настройка Horizon.

## Замечания

- Двойная защита от параллелизма: `withoutOverlapping()` на уровне расписания +
  `ShouldBeUnique` на уровне job (шаг 09).
- Lock'и (`withoutOverlapping`, `onOneServer`, `ShouldBeUnique`) требуют рабочего кэш-драйвера.

## Definition of Done

- [ ] `Schedule::command('catalog:import-products')` в `routes/console.php` с `withoutOverlapping`/`onOneServer`/`runInBackground`.
- [ ] Частота согласована с обновлением фида.
- [ ] В supervisor/cron есть процесс планировщика и воркер очереди `imports`.
