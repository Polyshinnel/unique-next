<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Domain\Catalog\Models\Category;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Категория')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Название категории')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Select::make('parent_id')
                            ->label('Родительская категория')
                            ->placeholder('Не указана')
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->relationship(
                                name: 'parent',
                                titleAttribute: 'name',
                                modifyQueryUsing: static function ($query, ?Category $record) {
                                    if ($record instanceof Category && $record->exists) {
                                        $query->whereKeyNot($record->getKey());
                                    }
                                }
                            )
                            ->getOptionLabelFromRecordUsing(static fn (Category $record): string => $record->name),
                    ]),
                Section::make('SEO')
                    ->schema([
                        TextInput::make('title')
                            ->label('Title')
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
}
