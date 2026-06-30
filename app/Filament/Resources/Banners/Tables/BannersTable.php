<?php

namespace App\Filament\Resources\Banners\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BannersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Изображение')
                    ->disk('public')
                    ->square(),
                TextColumn::make('title')
                    ->label('Заголовок')
                    ->searchable()
                    ->limit(40),
                TextColumn::make('text')
                    ->label('Текст')
                    ->limit(60)
                    ->toggleable(),
                TextColumn::make('button_one_text')
                    ->label('Кнопка 1')
                    ->placeholder('Не указана')
                    ->toggleable(),
                TextColumn::make('button_two_text')
                    ->label('Кнопка 2')
                    ->placeholder('Не указана')
                    ->toggleable(),
                TextColumn::make('sort_order')
                    ->label('Сортировка')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Обновлена')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
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
}
