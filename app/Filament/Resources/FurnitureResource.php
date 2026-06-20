<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FurnitureResource\Pages;
use App\Models\Furniture;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;

class FurnitureResource extends Resource
{
    protected static ?string $model = Furniture::class;

    protected static ?string $navigationIcon = 'heroicon-o-office-building';

    protected static ?string $navigationLabel = 'Furniture';

    protected static ?string $navigationGroup = 'Furniture Rentals';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([

            Grid::make(2)->schema([

                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('category')
                    ->required()
                    ->maxLength(255),

                TextInput::make('price_per_month')
                    ->numeric()
                    ->required(),

                Toggle::make('is_available')
                    ->default(true),

                TextInput::make('image_url')
                    ->label('Image URL')
                    ->columnSpan(2),

                Textarea::make('description')
                    ->rows(5)
                    ->columnSpan(2),

            ]),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([

                Tables\Columns\TextColumn::make('id')
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category')
                    ->searchable(),

                Tables\Columns\TextColumn::make('price_per_month')
                    ->label('Price / Month')
                    ->money('INR'),

                Tables\Columns\IconColumn::make('is_available')
                    ->boolean()
                    ->label('Available'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y'),
            ])
            ->filters([

                SelectFilter::make('is_available')
                    ->options([
                        1 => 'Available',
                        0 => 'Unavailable',
                    ]),

                SelectFilter::make('category')
                    ->options(
                        Furniture::query()
                            ->distinct()
                            ->pluck('category', 'category')
                            ->toArray()
                    ),

            ])
            ->actions([

                Tables\Actions\Action::make('mark_available')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->action(fn (Furniture $record) => $record->update([
                        'is_available' => true,
                    ]))
                    ->visible(fn (Furniture $record) => !$record->is_available),

                Tables\Actions\Action::make('mark_unavailable')
                    ->color('danger')
                    ->icon('heroicon-o-x')
                    ->action(fn (Furniture $record) => $record->update([
                        'is_available' => false,
                    ]))
                    ->visible(fn (Furniture $record) => $record->is_available),

                Tables\Actions\EditAction::make(),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([

                Tables\Actions\DeleteBulkAction::make(),

                Tables\Actions\BulkAction::make('mark_available')
                    ->action(fn ($records) => $records->each->update([
                        'is_available' => true,
                    ])),

                Tables\Actions\BulkAction::make('mark_unavailable')
                    ->action(fn ($records) => $records->each->update([
                        'is_available' => false,
                    ])),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFurniture::route('/'),
            'create' => Pages\CreateFurniture::route('/create'),
            'edit' => Pages\EditFurniture::route('/{record}/edit'),
        ];
    }
}