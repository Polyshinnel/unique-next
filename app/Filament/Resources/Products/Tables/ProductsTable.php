<?php

namespace App\Filament\Resources\Products\Tables;

use App\Domain\Catalog\Models\Product;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('main_image')
                    ->label('Изображение')
                    ->state(static fn (Product $record): ?string => self::resolveMainImagePath($record))
                    ->disk('public')
                    ->visibility('public')
                    ->square()
                    ->imageSize(72),
                TextColumn::make('sku')
                    ->label('Артикул')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Название товара')
                    ->searchable()
                    ->sortable()
                    ->width('360px')
                    ->wrap(),
                TextColumn::make('productStatus.name')
                    ->label('Статус')
                    ->placeholder('Не указан')
                    ->badge()
                    ->color(static fn (Product $record): string => $record->productStatus?->color ?: 'gray')
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('Категория товара')
                    ->placeholder('Не указана')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('price')
                    ->label('Цена')
                    ->state(static fn (Product $record): string => self::formatPrice($record->price))
                    ->sortable(),
            ])
            ->defaultSort('name')
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                //
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
