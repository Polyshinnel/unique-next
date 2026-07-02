# Шаг 18. Frontend build and SSR verification

## Цель

Проверить, что каталог работает как SSR-страница с реальными товарами и проходит production build.

## Зависимости

- Frontend миграция из шагов 11-16.
- Backend API из шагов 07-09.
- Индексы из шага 17 желательны, но не обязательны для функциональной проверки.

## Команды

- `npm run build`
- `curl http://localhost:28080/catalog`
- `curl http://localhost:28080/catalog?page=2`
- `curl "http://localhost:28080/catalog?search=16%D0%9A20"`

## Задачи

1. Запустить backend/frontend окружение проекта.
2. Выполнить `npm run build`.
3. Проверить SSR HTML root каталога:
   - ответ содержит реальные названия товаров;
   - карточки есть в HTML без ожидания client-side fetch.
4. Проверить пагинацию:
   - `/catalog?page=2` открывается;
   - в HTML другие товары.
5. Проверить поиск:
   - `/catalog?search=16%D0%9A20` возвращает релевантные результаты.
6. Проверить реальную category page:
   - взять slug из БД или API filters;
   - открыть `/catalog/{category-path}`.
7. Проверить фильтры вручную:
   - регион;
   - категория;
   - доступность;
   - состояние.
8. Проверить сортировку:
   - `sort=price_desc`;
   - `sort=price_asc`.
9. Проверить detail page:
   - canonical URL открывается;
   - неканонический URL редиректит.

## Важные правила

- Цель проверки — убедиться, что товары есть именно в HTML ответа.
- Query params должны работать без клиентского JavaScript.
- Если build падает из-за типов API, исправлять типы, а не отключать проверки.

## Готово, когда

- `npm run build` проходит.
- `curl /catalog` показывает реальные товары в HTML.
- Все пункты manual checklist из `CatalogPlan.md` пройдены.
