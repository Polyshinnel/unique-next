# Шаг 08 — Управление `page_seo` через Filament

## Цель

Дать админам CRUD-интерфейс для таблицы `page_seo` в существующей админ-панели Filament
(`/admin`), чтобы редактировать `title`/`description`/`og_image` без правки кода и сидера.

## Контекст проекта

- Установлен **Filament `^5.6`** (unified Schemas: пакет `filament/schemas`).
- Панель настроена в `app/Providers/Filament/AdminPanelProvider.php`:
  - `id('admin')`, `path('admin')`, `login()`;
  - **авто-дискавери** ресурсов: `->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')`.
  - Значит, достаточно создать класс в `app/Filament/Resources` — регистрировать вручную не нужно.
- `App\Models\User` реализует `FilamentUser`, `canAccessPanel()` возвращает `true` (доступ открыт всем аутентифицированным).
- Каталог `app/Filament` пока отсутствует — создаётся генератором.

## Действия

### 1. Сгенерировать ресурс

Надёжнее всего сгенерировать стабы под установленную версию Filament, затем донастроить:

```bash
php artisan make:filament-resource PageSeo --generate
```

> Модель лежит в `App\Domain\Seo\Models\PageSeo`, а генератор по умолчанию ищет её в
> `App\Models`. Поэтому:
> - если артизан предложит выбрать/создать модель — **не создавайте** новую в `App\Models`;
> - после генерации поправьте `use`/`$model` на `App\Domain\Seo\Models\PageSeo` (см. ниже);
> - удалите ошибочно созданный `app/Models/PageSeo.php`, если он появился.

Будут созданы:

```text
app/Filament/Resources/
├── PageSeoResource.php
└── PageSeoResource/
    └── Pages/
        ├── CreatePageSeo.php
        ├── EditPageSeo.php
        └── ListPageSeos.php
```

### 2. Привязать ресурс к доменной модели

В `PageSeoResource.php`:

```php
use App\Domain\Seo\Models\PageSeo;

protected static ?string $model = PageSeo::class;
```

### 3. Форма и таблица (ориентир под Filament v5 / Schemas)

Сгенерированный `--generate` код обычно уже корректен; ниже — целевой вид для проверки/правки.

```php
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

public static function form(Schema $schema): Schema
{
    return $schema->components([
        Section::make('Идентификация')
            ->columns(2)
            ->schema([
                TextInput::make('key')
                    ->label('Ключ')
                    ->required()
                    ->maxLength(64)
                    ->unique(ignoreRecord: true)
                    ->helperText('Машинный ключ для кода (home, services, …)'),
                TextInput::make('path')
                    ->label('URL-путь')
                    ->required()
                    ->maxLength(191)
                    ->unique(ignoreRecord: true)
                    ->helperText('Напр. / или /services'),
                TextInput::make('name')->label('Название')->maxLength(255),
                Toggle::make('is_active')->label('Активна')->default(true),
            ]),
        Section::make('SEO')
            ->schema([
                TextInput::make('title')->label('Title')->maxLength(255),
                Textarea::make('description')->label('Description')->maxLength(255)->rows(3),
                TextInput::make('og_image')->label('OG image')->maxLength(512),
            ]),
    ]);
}

public static function table(Table $table): Table
{
    return $table
        ->columns([
            TextColumn::make('key')->label('Ключ')->searchable()->sortable(),
            TextColumn::make('path')->label('Путь')->searchable(),
            TextColumn::make('name')->label('Название')->searchable()->toggleable(),
            TextColumn::make('title')->label('Title')->limit(40)->toggleable(),
            IconColumn::make('is_active')->label('Активна')->boolean()->sortable(),
            TextColumn::make('updated_at')->label('Обновлена')->dateTime()->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ])
        ->defaultSort('key')
        ->recordActions([
            EditAction::make(),
        ])
        ->toolbarActions([
            BulkActionGroup::make([
                DeleteBulkAction::make(),
            ]),
        ]);
}
```

> Заметки по версии: в Filament v4/v5 форма принимает `Filament\Schemas\Schema` и метод
> `->components([...])` (вместо старого `Form`/`->schema([...])`); табличные действия —
> `->recordActions()` / `->toolbarActions()` из неймспейса `Filament\Actions\`.
> Если `--generate` выдал другой синтаксис — он соответствует установленной версии,
> придерживайтесь его.

### 4. Подписи навигации (опционально)

```php
use Filament\Support\Icons\Heroicon;
use BackedEnum;

protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMagnifyingGlass;
protected static ?string $navigationLabel = 'SEO страниц';
protected static ?string $modelLabel = 'SEO страницы';
protected static ?string $pluralModelLabel = 'SEO страниц';
protected static ?int $navigationSort = 90;
```

(Иконку можно задать строкой `'heroicon-o-magnifying-glass'`, если enum недоступен.)

## Валидация (важно)

- `key` и `path` — `->required()->unique(ignoreRecord: true)`, чтобы не плодить дубликаты
  и не ломать API-поиск (шаг 05). Лимиты длины совпадают с миграцией: `key` 64, `path` 191,
  `og_image` 512, `title`/`description` 255.
- `path` желательно нормализовать так же, как в сидере/контроллере (ведущий слэш, без
  trailing). Можно повесить `->dehydrateStateUsing(fn ($state) => '/'.ltrim(rtrim($state,'/'),'/'))`
  или мутатор на модели.

## Кэш SEO

Записи отдаются фронту с `next.revalidate` (шаг 06), поэтому изменения в админке появятся
после ревалидации. Если нужно мгновенно — добавить инвалидацию по тегу `seo:{key}` (например,
дёргать revalidate-эндпоинт Next.js из `EditPageSeo::afterSave()`); это опциональное улучшение.

## Проверка

- `php artisan make:filament-resource` отработал, классы на месте.
- Зайти в `/admin`, открыть раздел «SEO страниц»: список 14 записей (после сидера, шаг 04).
- Создать/отредактировать запись; проверить срабатывание unique-валидации на `key`/`path`.
- Проверить, что фронтенд (шаг 07) получает обновлённые значения через API.

## Definition of Done

- [ ] `PageSeoResource` (+ страницы `List/Create/Edit`) создан в `app/Filament/Resources`.
- [ ] `$model` указывает на `App\Domain\Seo\Models\PageSeo` (без дубликата в `App\Models`).
- [ ] Форма содержит все поля; `key`/`path` — required + unique.
- [ ] Таблица со столбцами, поиском, сортировкой и действиями редактирования/удаления.
- [ ] Ресурс виден и работает в панели `/admin`.
