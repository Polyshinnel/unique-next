# Шаг 13. Frontend category and product catch-all route

## Цель

Перевести `resources/js/app/catalog/[...slug]/page.tsx` на real API для категорий и детальных страниц товара.

## Зависимости

- Frontend API client из шага 11.
- Backend category API из шага 08.
- Backend product detail API из шага 09.
- Backend page API из шага 07.

## Файлы

- Изменить: `resources/js/app/catalog/[...slug]/page.tsx`.

## Задачи

1. Получить `slug` как массив сегментов.
2. Определить тип route:
   - если последний сегмент числовой, это product id;
   - иначе это category path.
3. Для product route:
   - вызвать `getCatalogProduct(id)`;
   - если API вернул 404, вызвать `notFound()`;
   - сравнить текущий pathname с `product.canonicalHref`;
   - если отличается, сделать `permanentRedirect(product.canonicalHref)`;
   - отрендерить детальную страницу товара на данных API.
4. Для category route:
   - собрать `category_path = slug.join('/')`;
   - вызвать `getCatalogPage({ category_path, ...searchParams })`;
   - если API вернул 404, вызвать `notFound()`;
   - отрендерить `CatalogPageView`.
5. Удалить или отключить `generateStaticParams()` на моковых категориях/товарах.
6. Обновить `generateMetadata()`:
   - category title: `category.title || category.name`;
   - category description: `category.description || fallback`;
   - product title: `${product.title} | ЮНИК С`;
   - canonical: category/product href.

## Важные правила

- Новые товары и категории должны открываться без пересборки Next.
- Неканонический URL товара должен редиректить на canonical.
- Нельзя оставлять detail page на старом моковом `catalogProducts`.

## Проверка

- Реальная category page открывается по slug из БД.
- Реальный product открывается по `/catalog/{category-path}/{id}`.
- Несуществующая категория возвращает 404.
- Неканонический product URL редиректит.

## Готово, когда

- Catch-all route полностью работает от API.
- Моковые category/product helpers больше не нужны этому route.
