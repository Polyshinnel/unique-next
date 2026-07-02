<?php

namespace App\Filament\Resources\Shipments\Tables;

use App\Domain\Shipment\Models\Shipment;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ShipmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('main_image')
                    ->label('Изображение')
                    ->state(static fn (Shipment $record): ?string => self::resolveMainImagePath($record))
                    ->disk('public')
                    ->visibility('public')
                    ->square()
                    ->imageSize(72),
                TextColumn::make('title')
                    ->label('Название отгрузки')
                    ->searchable()
                    ->sortable()
                    ->width('360px')
                    ->wrap(),
                TextColumn::make('location')
                    ->label('Локация')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Не указана'),
                TextColumn::make('shipment_date')
                    ->label('Дата')
                    ->date('d.m.Y')
                    ->sortable(),
                TextColumn::make('tags.name')
                    ->label('Теги')
                    ->badge()
                    ->separator(',')
                    ->searchable(),
                IconColumn::make('is_active')
                    ->label('Активна')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label('Сортировка')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('shipment_date', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function resolveMainImagePath(Shipment $record): ?string
    {
        $mainImage = $record->images->firstWhere('is_main', true)
            ?? $record->images->first();

        return $mainImage?->file_path;
    }
}
