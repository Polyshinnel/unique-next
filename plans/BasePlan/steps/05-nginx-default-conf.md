# Шаг 1.5 — `docker/nginx/default.conf`

**Этап:** 1. Docker-инфраструктура  
**Статус:** [x] Выполнен

## Описание

Nginx конфигурация внутри контейнера `app`. Nginx выполняет две роли:

1. **FastCGI proxy** для PHP-FPM (Laravel API, Horizon UI, и т.д.)
2. **Reverse proxy** для локального Next.js SSR-процесса внутри того же контейнера `app`

## Содержимое файла

```nginx
server {
    listen 80;
    server_name _;
    root /var/www/html/public;
    index index.php;

    charset utf-8;
    client_max_body_size 64M;

    # Laravel: всё что начинается с /api, /sanctum, /horizon, /telescope, /_ignition
    location ~ ^/(api|sanctum|horizon|telescope|_ignition)(/.*)? {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ^~ /_next/ {
        proxy_pass http://127.0.0.1:3000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    # Next.js SSR — всё остальное проксируем в локальный процесс внутри app
    location / {
        proxy_pass http://127.0.0.1:3000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

}
```

## Маршрутизация запросов

| Паттерн URL | Куда направляется | Описание |
|---|---|---|
| `/api/*` | PHP-FPM (Laravel) | REST API |
| `/sanctum/*` | PHP-FPM (Laravel) | CSRF cookie, аутентификация |
| `/horizon/*` | PHP-FPM (Laravel) | Horizon dashboard |
| `/telescope/*` | PHP-FPM (Laravel) | Telescope debug |
| `/_ignition/*` | PHP-FPM (Laravel) | Ignition error page |
| `/*.php` | PHP-FPM (Laravel) | Любые PHP-файлы |
| `/_next/*` | Next.js `127.0.0.1:3000` | Next.js assets, HMR |
| `/*` (остальное) | Next.js `127.0.0.1:3000` | SSR фронтенд |

## Ключевые моменты

- **`client_max_body_size 64M`** — согласовано с `upload_max_filesize` в php.ini
- **WebSocket-заголовки** в proxy_pass к Next.js — нужны для HMR в dev-режиме
- **`location ^~ /_next/`** — явно проксирует Next.js assets и HMR
- **Нет отдельного regex-блока статики** — иначе Nginx может перехватить Next public assets (`/logo.png`, `/icon.svg`) и искать их в Laravel `public`
- **`fastcgi_hide_header X-Powered-By`** — скрываем версию PHP из заголовков ответа
- **Скрытие dotfiles** — `location ~ /\.(?!well-known).*` запрещает доступ к `.env`, `.git` и т.д.

## Зависимости

- Шаг 1.1 (директория `docker/nginx/` существует)

## Критерий завершения

Файл `docker/nginx/default.conf` создан, маршрутизация Laravel/Next.js внутри одного контейнера работает корректно.
