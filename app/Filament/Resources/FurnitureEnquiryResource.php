<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FurnitureEnquiryResource\Pages;
use App\Models\Furniture;
use App\Models\FurnitureEnquiry;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;

class FurnitureEnquiryResource extends Resource
{
    protected static ?string $model = FurnitureEnquiry::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-alt';

    protected static ?string $navigationLabel = 'Furniture Enquiries';

    protected static ?string $navigationGroup = 'Furniture Rentals';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([

            Grid::make(2)->schema([

                Select::make('furniture_id')
                    ->label('Furniture')
                    ->options(
                        Furniture::pluck('name', 'id')
                    )
                    ->searchable()
                    ->required(),

                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'accepted' => 'Accepted',
                        'declined' => 'Declined',
                        'completed' => 'Completed',
                    ])
                    ->required(),

                TextInput::make('name')
                    ->required(),

                TextInput::make('phone')
                    ->required(),

                TextInput::make('locality')
                    ->required(),

                Textarea::make('message')
                    ->rows(4)
                    ->columnSpan(2),

                Textarea::make('admin_notes')
                    ->rows(4)
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

                Tables\Columns\TextColumn::make('furniture.name')
                    ->label('Furniture')
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Customer')
                    ->searchable(),

                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),

                Tables\Columns\TextColumn::make('locality')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'accepted',
                        'danger' => 'declined',
                        'primary' => 'completed',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y H:i'),
            ])
            ->filters([

                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'accepted' => 'Accepted',
                        'declined' => 'Declined',
                        'completed' => 'Completed',
                    ]),

            ])
            ->actions([

                Tables\Actions\Action::make('accept')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->action(fn (FurnitureEnquiry $record) => $record->update([
                        'status' => 'accepted',
                    ]))
                    ->visible(fn (FurnitureEnquiry $record) => $record->status === 'pending'),

                Tables\Actions\Action::make('decline')
                    ->icon('heroicon-o-x')
                    ->color('danger')
                    ->action(fn (FurnitureEnquiry $record) => $record->update([
                        'status' => 'declined',
                    ]))
                    ->visible(fn (FurnitureEnquiry $record) => $record->status === 'pending'),

                Tables\Actions\Action::make('complete')
                    ->icon('heroicon-o-badge-check')
                    ->color('primary')
                    ->action(fn (FurnitureEnquiry $record) => $record->update([
                        'status' => 'completed',
                    ]))
                    ->visible(fn (FurnitureEnquiry $record) => $record->status === 'accepted'),

                Tables\Actions\EditAction::make(),

                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([

                Tables\Actions\DeleteBulkAction::make(),

                Tables\Actions\BulkAction::make('accept_selected')
                    ->action(fn ($records) => $records->each->update([
                        'status' => 'accepted',
                    ])),

                Tables\Actions\BulkAction::make('complete_selected')
                    ->action(fn ($records) => $records->each->update([
                        'status' => 'completed',
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
            'index' => Pages\ListFurnitureEnquiries::route('/'),
            'create' => Pages\CreateFurnitureEnquiry::route('/create'),
            'edit' => Pages\EditFurnitureEnquiry::route('/{record}/edit'),
        ];
    }
}