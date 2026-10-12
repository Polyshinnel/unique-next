<?php

namespace App\Filament\Pages;

use App\Models\TelegramSetting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;

class TelegramSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-paper-airplane';
    protected static ?string $navigationLabel = 'Telegram';
    protected static ?string $title = 'Настройки Telegram';
    protected static ?int $navigationSort = 96;
    protected static ?string $slug = 'telegram-settings';
    protected Width|string|null $maxContentWidth = Width::Large;

    /** @var array<string, mixed> | null */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(TelegramSetting::current()->attributesToArray());
    }

    public function getTitle(): string|Htmlable
    {
        return static::$title ?? 'Настройки Telegram';
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Получатель')
                ->description('Токен бота задаётся в TELEGRAM_BOT_TOKEN в файле .env.')
                ->schema([
                    TextInput::make('chat_id')
                        ->label('Chat ID')
                        ->required()
                        ->maxLength(255)
                        ->dehydrateStateUsing(fn (?string $state): string => trim($state ?? '')),
                ]),
            Section::make('HTTP прокси')
                ->schema([
                    Toggle::make('use_proxy')->label('Использовать прокси'),
                    TextInput::make('proxy_url')
                        ->label('Адрес Squid')
                        ->placeholder('http://squid:3128')
                        ->url()
                        ->required(fn (Get $get): bool => (bool) $get('use_proxy'))
                        ->maxLength(255),
                ]),
        ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('telegram-settings-form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('save')->label('Сохранить')->submit('save'),
                    ])->alignment(Alignment::Start),
                ]),
        ]);
    }

    public function save(): void
    {
        TelegramSetting::current()->update($this->form->getState());

        Notification::make()->title('Настройки Telegram сохранены')->success()->send();
    }
}
