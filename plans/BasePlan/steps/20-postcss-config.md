# Шаг 4.2 — Настроить PostCSS

**Этап:** 4. Интеграция Mantine UI  
**Статус:** [ ] Не выполнен

## Описание

Создать конфигурацию PostCSS для корректной работы Mantine UI. Mantine использует CSS variables и требует специальных PostCSS-плагинов.

## Содержимое `frontend/postcss.config.mjs`

```javascript
export default {
  plugins: {
    'postcss-preset-mantine': {},
    'postcss-simple-vars': {
      variables: {
        'mantine-breakpoint-xs': '36em',
        'mantine-breakpoint-sm': '48em',
        'mantine-breakpoint-md': '62em',
        'mantine-breakpoint-lg': '75em',
        'mantine-breakpoint-xl': '88em',
      },
    },
  },
};
```

## Пояснение

### `postcss-preset-mantine`

- Обрабатывает CSS-миксины Mantine (`light-dark()`, `hover()`, etc.)
- Обязательный плагин для корректной работы стилей

### `postcss-simple-vars`

- Определяет переменные для breakpoints Mantine
- Используются в responsive-стилях компонентов
- Значения в `em` — стандарт Mantine

### Breakpoints

| Переменная | Значение | Пиксели (при 16px base) |
|---|---|---|
| xs | 36em | 576px |
| sm | 48em | 768px |
| md | 62em | 992px |
| lg | 75em | 1200px |
| xl | 88em | 1408px |

## Зависимости

- Шаг 4.1 (PostCSS-пакеты установлены)

## Критерий завершения

Файл `frontend/postcss.config.mjs` создан. Mantine-стили корректно обрабатываются при сборке.
