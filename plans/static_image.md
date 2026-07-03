# Статические изображения под конвертацию в WebP

Ниже список растровых статических изображений, которые сейчас есть в проекте и потенциально стоит перевести в `webp`.

Важно:
- Основным источником, похоже, является `resources/js/public/assets/img/**`.
- Файлы в `public/assets/img/**` во многих случаях выглядят как дубли тех же картинок после копирования/сборки.
- `svg` и уже готовые `webp` сюда не включал.

## Приоритет 1: самые тяжелые

Эти файлы дадут максимальный выигрыш по весу и скорости загрузки.

| Размер | Исходный файл |
|---|---|
| 1.75 MB | `resources/js/public/assets/img/about-banner.png` | done
| 1.69 MB | `resources/js/public/assets/img/contact-banner.png` | done
| 1.26 MB | `resources/js/public/assets/img/catalog-banner.png` | done
| 1000 KB | `resources/js/public/assets/img/services/instrument-page/11.jpg` | done
| 990 KB | `resources/js/public/assets/img/services/instrument-page/4.jpg` | done
| 955 KB | `resources/js/public/assets/img/services/instrument-page/6.jpg` | done
| 946 KB | `resources/js/public/assets/img/services/instrument-page/13.jpg` | done
| 881 KB | `resources/js/public/assets/img/services/instrument-page/7.jpg` | done
| 843 KB | `resources/js/public/assets/img/services/instrument-page/15.jpg` | done
| 827 KB | `resources/js/public/assets/img/services/import-page/gallery/8.jpg` |
| 820 KB | `resources/js/public/assets/img/services/instrument-page/14.jpg` | done
| 785 KB | `resources/js/public/assets/img/services/import-page/gallery/7.jpg` | done
| 782 KB | `resources/js/public/assets/img/services/import-page/gallery/4.jpg` | done
| 761 KB | `resources/js/public/assets/img/services/instrument-page/8.jpg` | done
| 742 KB | `resources/js/public/assets/img/services/instrument-page/2.jpg` | done
| 741 KB | `resources/js/public/assets/img/services/instrument-page/12.jpg` | done
| 729 KB | `resources/js/public/assets/img/services/instrument-page/9.jpg` | done
| 641 KB | `resources/js/public/assets/img/services/import-page/gallery/1.jpg` | done
| 635 KB | `resources/js/public/assets/img/services/import-page/gallery/5.jpg` | done
| 631 KB | `resources/js/public/assets/img/slide-4.jpg` | deleted(загрузка через панель пользователем)
| 631 KB | `resources/js/public/assets/img/services/instrument-page/5.jpg` | done
| 629 KB | `resources/js/public/assets/img/otgruzki-banner.JPEG` | done
| 610 KB | `resources/js/public/assets/img/services/import-page/gallery/6.jpg` | done 
| 604 KB | `resources/js/public/assets/img/services/import-page/gallery/3.jpg` | done
| 597 KB | `resources/js/public/assets/img/services/instrument-page/10.jpg` | done
| 561 KB | `resources/js/public/assets/img/slide-3.jpg` | deleted(загрузка через панель пользователем)
| 554 KB | `resources/js/public/assets/img/services-banner.JPEG` | done
| 511 KB | `resources/js/public/assets/img/services/instrument-page/3.jpg` | done

## Приоритет 2: средние по весу

| Размер | Исходный файл |
|---|---|
| 494 KB | `resources/js/public/assets/img/services/import-page/gallery/2.jpg` | done
| 487 KB | `resources/js/public/assets/img/services/instrument-page/1.jpg` | done
| 440 KB | `resources/js/public/assets/img/about/main.jpeg` | done
| 425 KB | `resources/js/public/assets/img/slide-2.jpg` | deleted(загрузка через панель пользователем)
| 421 KB | `resources/js/public/assets/img/ohrana-truda.png` | done
| 392 KB | `resources/js/public/assets/img/unique-logo.png` | done
| 379 KB | `resources/js/public/assets/img/services/import-page/gallery/9.jpg` | done
| 312 KB | `resources/js/public/assets/img/about/map.jpg` | done
| 228 KB | `resources/js/public/assets/img/products/demo-1/6.jpg` | deleted(демо данные, больше не нужны)
| 204 KB | `resources/js/public/assets/img/products/demo-1/7.jpg` | deleted(демо данные, больше не нужны)
| 192 KB | `resources/js/public/assets/img/products/demo-1/8.jpg` | deleted(демо данные, больше не нужны)
| 180 KB | `resources/js/public/assets/img/products/demo-1/4.jpg` | deleted(демо данные, больше не нужны)
| 180 KB | `resources/js/public/assets/img/products/demo-1/10.jpg` | deleted(демо данные, больше не нужны)
| 176 KB | `resources/js/public/assets/img/products/demo-1/5.jpg` | deleted(демо данные, больше не нужны)
| 175 KB | `resources/js/public/assets/img/products/demo-1/1.jpg` | deleted(демо данные, больше не нужны)
| 174 KB | `resources/js/public/assets/img/slide-1.jpeg` | deleted(загрузка через панель пользователем)
| 174 KB | `resources/js/public/assets/img/products/demo-1/9.jpg` | deleted(демо данные, больше не нужны)
| 169 KB | `resources/js/public/assets/img/shipments/sbkj-sbfn-55/2.jpeg` | deleted(демо данные, больше не нужны)
| 156 KB | `resources/js/public/assets/img/shipments/sbkj-sbfn-55/1.jpeg` | deleted(демо данные, больше не нужны)
| 154 KB | `resources/js/public/assets/img/main.jpeg` | done
| 151 KB | `resources/js/public/assets/img/products/demo-1/2.jpg` | deleted(демо данные, больше не нужны)

## Приоритет 3: мелкие, но тоже можно перевести

| Размер | Исходный файл |
|---|---|
| 143 KB | `resources/js/public/assets/img/shipments/sbkj-sbfn-55/4.jpeg` | deleted(демо данные, больше не нужны)
| 135 KB | `resources/js/public/assets/img/shipments/sbkj-sbfn-55/5.jpeg` | deleted(демо данные, больше не нужны)
| 134 KB | `resources/js/public/assets/img/vacancy-img.jpeg` | done
| 127 KB | `resources/js/public/assets/img/shipments/sbkj-sbfn-55/3.jpeg` | deleted(демо данные, больше не нужны)
| 127 KB | `resources/js/public/assets/img/products/demo-1/3.jpg` | deleted(демо данные, больше не нужны)
| 116 KB | `resources/js/public/assets/img/shipments/sbkj-sbfn-55/6.jpeg` | deleted(демо данные, больше не нужны)
| 115 KB | `resources/js/public/assets/img/otgruzki-2.jpg` | deleted(демо данные, больше не нужны)
| 107 KB | `resources/js/public/assets/img/serv-4.jpg` | done
| 97 KB | `resources/js/public/assets/img/otgruzki-1.jpeg` | deleted(демо данные, больше не нужны)
| 96 KB | `resources/js/public/assets/img/otgruzki-3.jpeg` | deleted(демо данные, больше не нужны)
| 92 KB | `resources/js/public/assets/img/serv-2.jpg` | done
| 76 KB | `resources/js/public/assets/img/services/import-page/import-block.jpeg` |
| 69 KB | `resources/js/public/assets/img/serv-3.jpg` | done
| 68 KB | `resources/js/public/assets/img/serv-1.jpeg` | done
| 68 KB | `resources/js/public/assets/img/catalog.jpeg` | не будем менять
| 36 KB | `resources/js/public/assets/img/favicon.png` | не будем менять
| 19 KB | `resources/js/public/assets/img/unique-favicon.png` | не будем менять
| 18 KB | `resources/js/public/assets/img/map-pin.png` | не будем менять
| 4 KB | `public/favicon.png` | не будем менять

## Где эти картинки сейчас используются

### Баннеры и фоновые изображения

- `resources/js/public/assets/img/about-banner.png`
  Используется в `resources/js/app/globals.css`
- `resources/js/public/assets/img/contact-banner.png`
  Используется в `resources/js/app/globals.css`
- `resources/js/public/assets/img/catalog-banner.png`
  Используется в `resources/js/app/globals.css`
- `resources/js/public/assets/img/otgruzki-banner.JPEG`
  Используется в `resources/js/app/globals.css` и как fallback в `app/Http/Resources/Shipment/ShipmentResource.php`
- `resources/js/public/assets/img/services-banner.JPEG`
  Используется в `resources/js/app/globals.css`
- `resources/js/public/assets/img/slide-2.jpg`
  Используется в `resources/js/app/globals.css` и `resources/js/components/home/heroSlides.ts`
- `resources/js/public/assets/img/slide-3.jpg`
  Используется в `resources/js/app/globals.css` и `resources/js/components/home/heroSlides.ts`
- `resources/js/public/assets/img/slide-4.jpg`
  Используется в `resources/js/app/globals.css` и `resources/js/components/home/heroSlides.ts`

### Обычные картинки в компонентах и страницах

- `resources/js/public/assets/img/main.jpeg`
  `resources/js/app/page.tsx`
- `resources/js/public/assets/img/vacancy-img.jpeg`
  `resources/js/app/vacancy/page.tsx`
- `resources/js/public/assets/img/ohrana-truda.png`
  `resources/js/app/ohrana-truda/page.tsx`
- `resources/js/public/assets/img/about/main.jpeg`
  `resources/js/components/about/AboutPageView.tsx`
- `resources/js/public/assets/img/about/map.jpg`
  `resources/js/components/about/AboutPageView.tsx`
- `resources/js/public/assets/img/unique-logo.png`
  `resources/js/components/layout/HeaderClient.tsx`, `resources/js/components/layout/Footer.tsx`
- `resources/js/public/assets/img/map-pin.png`
  `resources/js/components/contacts/YandexMap.tsx`
- `resources/js/public/assets/img/catalog.jpeg`
  fallback в `resources/js/components/catalog/ProductCard.tsx`, `app/Http/Resources/Catalog/CatalogProductCardResource.php`, `app/Http/Resources/Catalog/CatalogProductDetailResource.php`
- `resources/js/public/assets/img/serv-1.jpeg`
  `resources/js/lib/site-content.ts`
- `resources/js/public/assets/img/serv-2.jpg`
  `resources/js/lib/site-content.ts`
- `resources/js/public/assets/img/serv-3.jpg`
  `resources/js/lib/site-content.ts`
- `resources/js/public/assets/img/serv-4.jpg`
  `resources/js/lib/site-content.ts`

### Галереи

- `resources/js/public/assets/img/services/import-page/import-block.jpeg`
  `resources/js/app/why-we/page.tsx`, `resources/js/app/services/import-oborudovaniya/page.tsx`
- `resources/js/public/assets/img/services/import-page/gallery/1.jpg` ... `9.jpg`
  `resources/js/app/services/import-oborudovaniya/page.tsx`
- `resources/js/public/assets/img/services/instrument-page/1.jpg` ... `15.jpg`
  `resources/js/app/services/prodazha-instrumenta/page.tsx`

### Дополнительные растровые файлы

- `resources/js/public/assets/img/products/demo-1/1.jpg` ... `10.jpg`
  В коде прямых ссылок не нашёл, но файлы лежат среди статических ассетов.
- `resources/js/public/assets/img/shipments/sbkj-sbfn-55/1.jpeg` ... `6.jpeg`
  В коде прямых ссылок не нашёл, но файлы лежат среди статических ассетов.
- `resources/js/public/assets/img/otgruzki-1.jpeg`
- `resources/js/public/assets/img/otgruzki-2.jpg`
- `resources/js/public/assets/img/otgruzki-3.jpeg`
  Прямых ссылок по коду не увидел, возможно используются как контентные изображения или заготовки.

## Что я бы рекомендовал конвертировать в первую очередь

1. Все баннеры: `about-banner`, `contact-banner`, `catalog-banner`, `otgruzki-banner`, `services-banner`.
2. Галереи `services/import-page/gallery/*` и `services/instrument-page/*` - там очень большой суммарный вес.
3. Слайды главной: `slide-2`, `slide-3`, `slide-4`.
4. Крупные одиночные изображения: `about/main.jpeg`, `ohrana-truda.png`, `unique-logo.png`, `about/map.jpg`.

## После конвертации

Когда ты сделаешь `webp`, я смогу:
- пройти по страницам и заменить пути на новые;
- отдельно обновить CSS-фоны;
- проверить fallback-ы в PHP-ресурсах и React-компонентах;
- при необходимости оставить `png/jpg` только там, где это действительно нужно.
