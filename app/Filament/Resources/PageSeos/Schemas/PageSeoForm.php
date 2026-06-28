<?php

namespace App\Filament\Resources\PageSeos\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PageSeoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Идентификация')
                    ->columns(2)
                    ->schema([
                        TextInput::make('key')
                            ->label('Ключ')
                            ->required()
                            ->maxLength(64)
                            ->unique(ignoreRecord: true)
                            ->helperText('Машинный ключ для кода: home, services, catalog и т.д.'),
                        TextInput::make('path')
                            ->label('URL-путь')
                            ->required()
                            ->maxLength(191)
                            ->unique(ignoreRecord: true)
                            ->dehydrateStateUsing(static function (?string $state): string {
                                $path = parse_url((string) $state, PHP_URL_PATH) ?? (string) $state;
                                $path = '/' . ltrim($path, '/');
                                $path = rtrim($path, '/');

                                return $path === '' ? '/' : $path;
                            })
                            ->helperText('Например: /, /services, /services/vykup'),
                        TextInput::make('name')
                            ->label('Название')
                            ->maxLength(255),
                        Toggle::make('is_active')
                            ->label('Активна')
                            ->default(true),
                    ]),
                Section::make('SEO')
                    ->schema([
                        TextInput::make('title')
                            ->label('Title')
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label('Description')
                            ->maxLength(255)
                            ->rows(3),
                        FileUpload::make('og_image')
                            ->label('OG image')
                            ->disk('public')
                            ->directory('seo')
                            ->visibility('public')
                            ->image()
                            ->imagePreviewHeight('200')
                            ->openable()
                            ->downloadable()
                            ->helperText('Файл будет сохранён в storage/app/public/seo и доступен по пути /storage/seo/...'),
                    ]),
            ]);
    }
}
