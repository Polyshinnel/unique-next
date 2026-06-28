# Шаг 05 — `FeedDownloader` + `FeedParser`

## Цель

Скачать XML-фид во временный файл и потоково разобрать его в DTO (шаг 04), не загружая весь
документ в память. Большой фид со всеми объявлениями и медиа разбирается через `XMLReader`.

## Расположение

`app/Domain/Catalog/Import/Services`, неймспейс `App\Domain\Catalog\Import\Services`.

## `FeedDownloader`

Назначение: получить XML по URL и сохранить во временный файл, вернуть путь.

```php
final class FeedDownloader
{
    public function download(string $url): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'catalog_feed_');

        $response = Http::timeout(config('catalog_import.http_timeout'))
            ->throw()
            ->sink($tmp)   // потоково в файл
            ->get($url);

        // Опц. проверка content-type на xml
        return $tmp;
    }
}
```

- Использовать `->sink($tmp)`, чтобы не держать тело ответа в памяти.
- `->throw()` — бросить исключение на не-2xx (job уйдёт в retry).
- Временный файл удалять после парсинга (`@unlink` в `finally` оркестратора).

## `FeedParser`

Назначение: потоково пройти XML и отдавать объявления по одному (генератор), плюс отдельные
методы для блоков справочников верхнего уровня.

```php
final class FeedParser
{
    /** Справочники верхнего уровня (вызывать первыми). */
    public function categories(string $path): iterable     { /* yield CategoryData */ }
    public function advertisementStatuses(string $path): iterable { /* yield StatusData */ }
    public function checkStatuses(string $path): iterable   { /* yield StatusData */ }
    public function installStatuses(string $path): iterable { /* yield StatusData */ }

    /** Объявления — генератор, по одному. */
    public function advertisements(string $path): iterable  { /* yield AdvertisementData */ }

    /** Атрибут export_date корня фида. */
    public function exportDate(string $path): ?string       { /* ... */ }
}
```

### Техника парсинга (XMLReader)

```php
$reader = new XMLReader();
$reader->open($path);

while ($reader->read()) {
    if ($reader->nodeType === XMLReader::ELEMENT && $reader->localName === 'advertisement') {
        $node = $reader->expand();                  // развернуть текущий узел
        $dom  = new DOMDocument();
        $sx   = simplexml_import_dom($dom->importNode($node, true));
        yield $this->mapAdvertisement($sx);         // SimpleXML удобен для одного узла
    }
}
$reader->close();
```

- Гибрид: `XMLReader` находит границы `<advertisement>`, `expand()` + `SimpleXML` маппит
  конкретный узел (память тратится только на одно объявление).
- CDATA-блоки (`main_characteristics`, `*_info`, комментарии) читать как строку «как есть».
- Атрибуты (`@id`) — через `$sx['id']`; приводить к `int`.
- `show_price`, `is_main_image` `1/0` → `bool`.

## Замечания

- Справочники и объявления — отдельные проходы по файлу (можно несколько `XMLReader::open`).
- Парсер **не** обращается к БД — только маппинг XML → DTO.
- Невалидные/неполные узлы логировать и пропускать (не валить весь разбор).

## Definition of Done

- [ ] `FeedDownloader::download()` сохраняет фид во временный файл (sink, timeout, throw).
- [ ] `FeedParser` потоково (`XMLReader`) отдаёт DTO справочников и объявлений.
- [ ] CDATA, атрибуты `@id`, булевы `1/0` корректно маппятся.
- [ ] Парсер не зависит от БД; ошибки узла не валят разбор.
