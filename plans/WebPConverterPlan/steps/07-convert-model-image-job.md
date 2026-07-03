# Шаг 07. Создать ConvertModelImageToWebpJob

## Цель

Создать универсальную queue job, которая конвертирует изображение конкретной модели/атрибута и обновляет путь в базе только если значение не изменилось с момента постановки задачи.

## Зависимости

- Завершен шаг 03.
- Завершен шаг 06.
- Очередь в проекте уже используется, в production ожидается Redis + Horizon.

## Файлы

- Создать: `app/Domain/Media/Jobs/ConvertModelImageToWebpJob.php`.

## Конструктор

```php
public function __construct(
    public readonly string $modelClass,
    public readonly int|string $modelKey,
    public readonly string $attribute,
    public readonly string $disk,
    public readonly string $sourcePath,
) {}
```

## Задачи

1. Создать директорию `app/Domain/Media/Jobs`, если ее нет.
2. Сделать job очередной:
   - реализовать `ShouldQueue`;
   - использовать стандартные Laravel traits для dispatch/queue/serialization.
3. Настроить очередь:
   - queue: `config('images.webp.queue')`;
   - attempts: `3`;
   - backoff: `[60, 300, 900]`.
4. В `handle(ImageToWebpConverter $converter)` реализовать поток:
   - найти модель: `$modelClass::query()->find($modelKey)`;
   - если модели нет, завершить без ошибки;
   - если текущее значение `$model->{$attribute}` не равно `$sourcePath`, завершить без обновления;
   - если `$converter->shouldConvertPath($sourcePath)` вернул `false`, завершить;
   - выполнить конвертацию и получить `$webpPath`;
   - заново перечитать модель из базы;
   - еще раз проверить, что поле равно `$sourcePath`;
   - обновить атрибут на `$webpPath` без повторного запуска observer.
5. Для обновления использовать `withoutEvents`:

```php
$modelClass::withoutEvents(function () use ($model, $attribute, $webpPath): void {
    $model->forceFill([$attribute => $webpPath])->save();
});
```

6. После успешного обновления удалить исходник, если:
   - `config('images.webp.delete_original_after_conversion')` истинный;
   - исходный путь отличается от итогового;
   - ни одна актуальная запись этой же модели/атрибута больше не ссылается на исходный путь.
7. При `ImageConversionException` логировать warning с моделью, ключом, атрибутом, disk и source path.
8. Не перезаписывать новое изображение, если пользователь успел заменить файл до выполнения job.

## Важные правила

- Job универсальная: не создавать отдельные job-классы для `Banner`, `Product`, `Category`, `ShipmentImage`.
- Проверка совпадения пути нужна дважды: до конвертации и перед обновлением.
- Если исходный файл отсутствует, пользовательский сценарий не должен падать.

## Проверка

Покрыть на шаге 12:

- `.jpg` и `.png` заменяются на `.webp`;
- исходник удаляется после успешной замены при включенном флаге;
- измененное поле не перетирается старой job;
- удаленная модель не вызывает ошибку;
- отсутствующий файл логируется и не меняет БД.

## Готово, когда

- Job создана и ставится в очередь `images` по конфигу.
- Обновление модели защищено от гонок.
- Удаление исходника выполняется только после успешной замены.
