<?php

namespace App\Filament\Resources\Shipments\Schemas;

use App\Domain\Shipment\Models\ShipmentTag;
use App\Filament\Support\ImageUpload;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ShipmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('SEO и URL')
                    ->columns(2)
                    ->schema([
                        TextInput::make('slug')
                            ->label('Slug')
                            ->maxLength(160)
                            ->unique(ignoreRecord: true)
                            ->dehydrateStateUsing(static fn (?string $state): ?string => filled($state) ? Str::slug(trim($state)) : null)
                            ->helperText('Если заполнен, будет использоваться в ссылке вместо ID.'),
                        TextInput::make('seo_title')
                            ->label('SEO title')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Textarea::make('seo_description')
                            ->label('SEO description')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
                Section::make('Отгрузка')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('Название отгрузки')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('location')
                            ->label('Локация отгрузки')
                            ->maxLength(255),
                        DatePicker::make('shipment_date')
                            ->label('Дата отгрузки')
                            ->required()
                            ->native(false),
                        Textarea::make('short_description')
                            ->label('Короткое описание')
                            ->rows(3)
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label('Описание отгрузки')
                            ->rows(6)
                            ->columnSpanFull(),
                        Select::make('tags')
                            ->label('Теги отгрузки')
                            ->relationship('tags', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('Название тега')
                                    ->required()
                                    ->dehydrateStateUsing(static fn (?string $state): ?string => filled($state) ? trim($state) : null)
                                    ->maxLength(255)
                                    ->rules([
                                        Rule::unique('shipment_tags', 'name'),
                                    ]),
                            ])
                            ->createOptionUsing(static function (array $data): int {
                                return (int) ShipmentTag::query()
                                    ->firstOrCreate(['name' => trim((string) $data['name'])])
                                    ->getKey();
                            })
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->label('Активна')
                            ->default(true),
                        TextInput::make('sort_order')
                            ->label('Порядок сортировки')
                            ->numeric()
                            ->default(0)
                            ->required(),
                    ]),
                Section::make('Изображения отгрузки')
                    ->schema([
                        Repeater::make('images')
                            ->label('Изображения')
                            ->relationship('images')
                            ->orderColumn('sort_order')
                            ->reorderable()
                            ->addActionLabel('Добавить изображение')
                            ->schema([
                                ImageUpload::webpConvertibleUpload(
                                    FileUpload::make('file_path')->label('Изображение'),
                                    disk: 'public',
                                    directory: 'shipments',
                                )
                                    ->image()
                                    ->imagePreviewHeight('180')
                                    ->openable()
                                    ->downloadable()
                                    ->required()
                                    ->columnSpanFull()
                                    ->helperText('Файл будет сохранён сразу, затем автоматически преобразован в WebP в фоне.'),
                                Toggle::make('is_main')
                                    ->label('Главное изображение'),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
