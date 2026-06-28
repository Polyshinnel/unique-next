# Шаг 10 — `ImportProductsCommand`

## Цель

Artisan-команда — точка входа для импорта. Ставит `ImportProductsJob` в очередь (вручную или
из планировщика). Поддерживает опции URL и режима обновления.

## Расположение

`app/Domain/Catalog/Import/Commands/ImportProductsCommand.php`,
неймспейс `App\Domain\Catalog\Import\Commands`.

## Реализация

```php
final class ImportProductsCommand extends Command
{
    protected $signature = 'catalog:import-products
        {--update-existing : Обновлять изменившиеся поля существующих товаров}
        {--url= : Переопределить URL фида}';

    protected $description = 'Импорт товаров из XML-фида панели в очередь';

    public function handle(): int
    {
        ImportProductsJob::dispatch(
            url: $this->option('url') ?: config('catalog_import.feed_url'),
            updateExisting: (bool) $this->option('update-existing'),
        );

        $this->info('ImportProductsJob поставлен в очередь.');

        return self::SUCCESS;
    }
}
```

## Регистрация команды

- Laravel 11+/13 автоматически регистрирует команды из `app/Console/Commands`.
  Поскольку команда лежит в `app/Domain/...`, зарегистрировать её явно в
  `app/Console/Kernel.php` (метод `commands()`/`$commands`) или через
  `withCommands([...])` в `bootstrap/app.php`.
- Проверить: `php artisan list | grep catalog:import-products`.

## Использование

```bash
# Обычный прогон (строго пропускать существующие)
php artisan catalog:import-products

# С обновлением изменившихся полей
php artisan catalog:import-products --update-existing

# С кастомным URL (например, тестовый фид)
php artisan catalog:import-products --url=https://example.com/feed.xml
```

## Замечания

- Команда **не** выполняет импорт синхронно — только диспатчит job (шаг 09).
- Значения по умолчанию берутся из `config('catalog_import.*')` (шаг 01).
- Для немедленного синхронного прогона при отладке можно временно использовать
  `ImportProductsJob::dispatchSync(...)`.

## Definition of Done

- [ ] Команда `catalog:import-products` с опциями `--update-existing` и `--url`.
- [ ] Диспатчит `ImportProductsJob` с корректными аргументами.
- [ ] Команда зарегистрирована и видна в `php artisan list`.
