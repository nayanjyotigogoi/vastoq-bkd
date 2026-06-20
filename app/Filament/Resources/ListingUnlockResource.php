<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ListingUnlockResource\Pages;
use App\Models\Coupon;
use App\Models\Listing;
use App\Models\ListingUnlock;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;

class ListingUnlockResource extends Resource
{
    protected static ?string $model = ListingUnlock::class;

    protected static ?string $navigationIcon = 'heroicon-o-lock-open';

    protected static ?string $navigationLabel = 'Listing Unlocks';

    protected static ?string $navigationGroup = 'Revenue & Analytics';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([

            Grid::make(2)->schema([

                Select::make('listing_id')
                    ->label('Listing')
                    ->options(
                        Listing::pluck('title', 'id')
                    )
                    ->searchable()
                    ->required(),

                Select::make('user_id')
                    ->label('User')
                    ->options(
                        User::pluck('name', 'id')
                    )
                    ->searchable()
                    ->required(),

                Select::make('coupon_id')
                    ->label('Coupon')
                    ->options(
                        Coupon::pluck('code', 'id')
                    )
                    ->searchable()
                    ->nullable(),

                TextInput::make('amount_paid')
                    ->numeric()
                    ->required(),

                DateTimePicker::make('expires_at')
                    ->nullable(),

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

                Tables\Columns\TextColumn::make('listing.title')
                    ->label('Listing')
                    ->searchable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable(),

                Tables\Columns\TextColumn::make('coupon.code')
                ->label('Coupon')
                ->getStateUsing(fn ($record) => $record->coupon?->code ?? '-'),

                Tables\Columns\TextColumn::make('amount_paid')
                    ->money('INR'),

                Tables\Columns\TextColumn::make('expires_at')
                ->getStateUsing(
                    fn ($record) => $record->expires_at
                        ? $record->expires_at->format('d M Y H:i')
                        : '-'
                ),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Unlocked At')
                    ->dateTime('d M Y H:i'),
            ])
            ->filters([

                SelectFilter::make('coupon_id')
                    ->label('Coupon Used')
                    ->options(
                        Coupon::pluck('code', 'id')
                    ),

                Filter::make('paid_unlocks')
                    ->query(fn ($query) => $query->where('amount_paid', '>', 0)),

                Filter::make('free_unlocks')
                    ->query(fn ($query) => $query->where('amount_paid', 0)),
            ])
            ->actions([

                Tables\Actions\ViewAction::make(),

                Tables\Actions\EditAction::make(),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([

                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListListingUnlocks::route('/'),
            'create' => Pages\CreateListingUnlock::route('/create'),
            'edit' => Pages\EditListingUnlock::route('/{record}/edit'),
        ];
    }
}