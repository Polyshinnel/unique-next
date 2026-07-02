# Шаг 01. Backend request validation

## Цель

Подготовить единый request-класс для входных параметров `GET /api/catalog/page`, чтобы backend получал уже провалидированные и нормализованные фильтры каталога.

## Зависимости

- Laravel validation.
- Существующие таблицы: `regions`, `equipment_availabilities`, `equipment_states`.

## Файлы

- Создать: `app/Http/Requests/Catalog/CatalogPageRequest.php`.
- Позже использовать в: `app/Http/Controllers/Api/CatalogPageController.php`.

## Задачи

1. Создать директорию `app/Http/Requests/Catalog`, если ее нет.
2. Создать `CatalogPageRequest`.
3. Разрешить request через `authorize(): bool`.
4. Добавить правила:
   - `page`: `nullable`, `integer`, `min:1`;
   - `region`: `nullable`, `integer`, `exists:regions,id`;
   - `availability`: `nullable`, `integer`, `exists:equipment_availabilities,id`;
   - `state`: `nullable`, `integer`, `exists:equipment_states,id`;
   - `sort`: `nullable`, `string`, `in:default,price_desc,price_asc`;
   - `search`: `nullable`, `string`, `max:100`;
   - `category_path`: `nullable`, `string`, `max:500`.
5. В `prepareForValidation()` привести пустые строки к `null`.
6. В `prepareForValidation()` нормализовать:
   - `page`: если пусто, `1`;
   - `sort`: если пусто, `default`;
   - `search`: `trim`, пустое значение в `null`;
   - `category_path`: trim слешей по краям, пустое значение в `null`.
7. Добавить метод-аксессор или DTO-метод для получения нормализованных значений, если это соответствует стилю проекта.

## Важные правила

- `perPage` не принимать из URL.
- Максимальная длина `search` — 100 символов.
- Максимальная длина `category_path` — 500 символов.
- Query params используют id справочников, а не русские названия.

## Проверка

- Невалидный `region`, `availability`, `state` должен давать validation error.
- `sort=unknown` должен давать validation error.
- `page=0` должен давать validation error.
- Пустые строки в query должны превращаться в отсутствие фильтра.

## Готово, когда

- `CatalogPageRequest` создан.
- Все параметры из плана валидируются.
- Нормализация не размазана по контроллерам.
