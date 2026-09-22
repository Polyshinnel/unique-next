# Инструкции для агента

## Технологический стек

- PHP 8.4, PHP-FPM и Laravel 13.
- Filament 5 для административной панели.
- Laravel Horizon для очередей и Laravel Sanctum для API-аутентификации.
- Intervention Image Laravel для обработки изображений.
- Next.js 16 с SSR, React 19 и TypeScript 6.
- Mantine UI 9, Tabler Icons, Swiper и Fancyapps UI на фронтенде.
- MySQL 8.4 как основная база данных.
- Redis 7 для кэша и очередей.
- Nginx как веб-сервер и reverse proxy.
- Supervisor для управления процессами внутри контейнера.
- Docker Compose для запуска окружения.
- PHPUnit 12 для тестов и Laravel Pint для форматирования PHP-кода.
- ESLint 9 с конфигурацией Next.js для проверки JavaScript/TypeScript-кода.

## Выполнение команд

Проект развёрнут в Docker-контейнерах. Все команды, связанные с приложением и зависимостями, запускай внутри основного контейнера `app` через:

```bash
docker compose exec app <команда>
```

Примеры:

```bash
docker compose exec app php artisan migrate
docker compose exec app php artisan test
docker compose exec app composer install
docker compose exec app npm run lint
docker compose exec app npm run build
```

Команды управления самим окружением выполняются с хоста, например:

```bash
docker compose up -d
docker compose down
docker compose ps
docker compose logs -f app
```

Не запускай `php`, `php artisan`, `composer`, `npm` и `node` напрямую на хосте, если это не требуется явно: рабочее окружение находится внутри контейнера `app`.
