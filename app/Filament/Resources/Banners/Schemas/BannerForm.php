<?php

namespace App\Filament\Resources\Banners\Schemas;

use App\Filament\Support\ImageUpload;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BannerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Баннер')
                    ->columns(2)
                    ->schema([
                        FileUpload::make('image')
                            ->label('Изображение')
                            ->disk('public')
                            ->directory('banners')
                            ->visibility('public')
                            ->rules([ImageUpload::rule()])
                            ->extraInputAttributes([
                                'accept' => ImageUpload::ACCEPT_ATTRIBUTE,
                            ])
                            ->imagePreviewHeight('200')
                            ->openable()
                            ->downloadable()
                            ->columnSpanFull()
                            ->helperText('Файл будет сохранён в storage/app/public/banners и доступен по пути /storage/banners/...'),
                        TextInput::make('title')
                            ->label('Заголовок баннера')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Textarea::make('text')
                            ->label('Текст баннера')
                            ->rows(4)
                            ->columnSpanFull(),
                        TextInput::make('button_one_text')
                            ->label('Название кнопки 1')
                            ->maxLength(255),
                        TextInput::make('button_one_url')
                            ->label('Ссылка кнопки 1')
                            ->maxLength(512)
                            ->placeholder('/services/vykup или https://example.com'),
                        TextInput::make('button_two_text')
                            ->label('Название кнопки 2')
                            ->maxLength(255),
                        TextInput::make('button_two_url')
                            ->label('Ссылка кнопки 2')
                            ->maxLength(512)
                            ->placeholder('/contacts или https://example.com'),
                        TextInput::make('sort_order')
                            ->label('Порядок сортировки')
                            ->numeric()
                            ->default(0)
                            ->required(),
                    ]),
            ]);
    }
}
