<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Domain\Catalog\Models\Product;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Товар')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Название товара'),
                        TextEntry::make('product_status_id')
                            ->label('Статус товара')
                            ->state(static fn (Product $record): string => $record->productStatus?->name ?? 'Не указан')
                            ->badge()
                            ->color(static fn (Product $record): string => $record->productStatus?->color ?: 'gray'),
                        TextEntry::make('tags')
                            ->label('Теги')
                            ->state(static fn (Product $record): array => $record->tags->pluck('name')->all())
                            ->badge()
                            ->color('gray')
                            ->placeholder('Не указаны'),
                        ImageEntry::make('main_image')
                            ->label('Главное изображение товара')
                            ->state(static fn (Product $record): ?string => self::resolveMainImagePath($record))
                            ->disk('public')
                            ->visibility('public')
                            ->imageHeight(220)
                            ->columnSpanFull(),
                        TextEntry::make('sku')
                            ->label('Артикул')
                            ->placeholder('Не указан'),
                        TextEntry::make('region_id')
                            ->label('Регион')
                            ->state(static fn (Product $record): string => $record->region?->name ?? 'Не указан'),
                        TextEntry::make('price')
                            ->label('Цена товара')
                            ->state(static fn (Product $record): string => self::formatPrice($record->price)),
                        TextEntry::make('show_price')
                            ->label('Показывать цену')
                            ->state(static fn (Product $record): string => $record->show_price ? 'Да' : 'Нет'),
                        TextEntry::make('price_comment')
                            ->label('Комментарий к цене')
                            ->placeholder('Не указан')
                            ->columnSpanFull(),
                    ]),
                Section::make('SEO')
                    ->schema([
                        TextInput::make('title')
                            ->label('Title')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3),
                        FileUpload::make('og_image')
                            ->label('OG image')
                            ->disk('public')
                            ->directory('catalog')
                            ->visibility('public')
                            ->image()
                            ->imagePreviewHeight('200')
                            ->openable()
                            ->downloadable()
                            ->helperText('Файл будет сохранён в storage/app/public/catalog и доступен по пути /storage/catalog/...'),
                    ]),
            ]);
    }

    private static function resolveMainImagePath(Product $record): ?string
    {
        $mainImage = $record->images->firstWhere('sort_order', 0)
            ?? $record->images->firstWhere('is_main', true)
            ?? $record->images->first();

        return $mainImage?->file_path;
    }

    private static function formatPrice(mixed $price): string
    {
        if ($price === null || $price === '') {
            return 'Не указана';
        }

        return number_format((float) $price, 2, ',', ' ').' ₽';
    }
}
