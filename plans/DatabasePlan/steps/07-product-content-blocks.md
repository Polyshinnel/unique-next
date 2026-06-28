# Шаг 07 — Контентные блоки товара (1:1)

## Цель

5 таблиц с отношением **1:1** к `products` (`product_id` unique), хранят сырой HTML в `content`.

| Таблица | Назначение | XML-источник | Связь в `Product` |
|---|---|---|---|
| `product_main_characteristics` | Основные характеристики | `main_characteristics` | `mainCharacteristics()` |
| `product_complectations` | Комплектация | `complectation` | `complectation()` |
| `product_technical_characteristics` | Технические характеристики | `technical_characteristics` | `technicalCharacteristics()` |
| `product_main_infos` | Главная информация | `main_info` | `mainInfo()` |
| `product_additional_infos` | Дополнительная информация | `additional_info` | `additionalInfo()` |

## Единая структура каждой таблицы

| Поле | Тип | Null | Описание |
|---|---|---|---|
| `id` | `id()` | нет | PK |
| `product_id` | `foreignId` → `products.id`, unique, onDelete cascade | нет | Товар (1:1) |
| `content` | `longText` | да | Сырой HTML |
| `timestamps` | — | да | Метки |

```php
Schema::create('product_main_characteristics', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->unique()->constrained()->cascadeOnDelete();
    $table->longText('content')->nullable();
    $table->timestamps();
});
```

> Повторить для каждой из 5 таблиц (5 отдельных файлов миграций), создаются **после** `products`.

## Модели

Неймспейс `App\Domain\Catalog\Models`:
`ProductMainCharacteristic`, `ProductComplectation`, `ProductTechnicalCharacteristic`,
`ProductMainInfo`, `ProductAdditionalInfo`.

- `$fillable`: `product_id`, `content`.
- Связь `product()` → `belongsTo(Product::class)`.
- Указать `$table`, т.к. имена нестандартны (`product_main_infos` и т.п.).

## Дополнение модели `Product`

Добавить hasOne-связи:

```php
public function mainCharacteristics() { return $this->hasOne(ProductMainCharacteristic::class); }
public function complectation()       { return $this->hasOne(ProductComplectation::class); }
public function technicalCharacteristics() { return $this->hasOne(ProductTechnicalCharacteristic::class); }
public function mainInfo()            { return $this->hasOne(ProductMainInfo::class); }
public function additionalInfo()      { return $this->hasOne(ProductAdditionalInfo::class); }
```

## Definition of Done

- [ ] 5 миграций созданы (после `products`), `product_id` unique + cascade.
- [ ] 5 моделей с `$table`, `$fillable`, `product()`.
- [ ] hasOne-связи в `Product`.
