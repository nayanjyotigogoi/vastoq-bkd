<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ListingResource\Pages;
use App\Models\Listing;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;

class ListingResource extends Resource
{
    protected static ?string $model = Listing::class;

    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'Listings';

    protected static ?string $navigationGroup = 'Property Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([

            Grid::make(2)->schema([

                Select::make('owner_id')
                    ->label('Owner')
                    ->options(
                        User::where('role', 'owner')
                            ->pluck('name', 'id')
                    )
                    ->searchable()
                    ->required(),

                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->required(),

                TextInput::make('title')
                    ->required()
                    ->maxLength(191)
                    ->columnSpan(2),

                Textarea::make('description')
                    ->rows(4)
                    ->columnSpan(2),

                Select::make('property_type')
                    ->options([
                        'room' => 'Room',
                        'shared_room' => 'Shared Room',
                        'flat' => 'Flat',
                        'house' => 'House',
                        'pg' => 'PG',
                        'office' => 'Office',
                        'shop' => 'Shop',
                        'warehouse' => 'Warehouse',
                    ])
                    ->required(),

                Select::make('bhk_type')
                    ->options([
                        'na' => 'N/A',
                        '1rk' => '1 RK',
                        '2rk' => '2 RK',
                        '1bhk' => '1 BHK',
                        '2bhk' => '2 BHK',
                        '3bhk' => '3 BHK',
                        '4bhk' => '4 BHK',
                        '5bhk' => '5 BHK',
                    ]),

                Select::make('furnishing')
                    ->options([
                        'unfurnished' => 'Unfurnished',
                        'semi_furnished' => 'Semi Furnished',
                        'fully_furnished' => 'Fully Furnished',
                    ])
                    ->required(),

                Select::make('listing_class')
                    ->options([
                        'residential' => 'Residential',
                        'commercial' => 'Commercial',
                    ])
                    ->required(),

                TextInput::make('rent_per_month')
                    ->numeric()
                    ->required(),

                TextInput::make('deposit')
                    ->numeric(),

                TextInput::make('locality')
                    ->required(),

                TextInput::make('city')
                    ->required(),

                TextInput::make('pincode'),

                TextInput::make('area_sqft')
                    ->numeric(),

                TextInput::make('floor_number')
                    ->numeric(),

                Select::make('gender_preference')
                    ->options([
                        'male' => 'Male',
                        'female' => 'Female',
                        'family' => 'Family',
                        'any' => 'Any',
                    ]),

                TextInput::make('latitude')
                    ->numeric(),

                TextInput::make('longitude')
                    ->numeric(),

                Textarea::make('address')
                    ->required()
                    ->columnSpan(2),

                Toggle::make('is_broker'),

                Toggle::make('is_featured'),

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

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('owner.name')
                    ->label('Owner')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('property_type')
                    ->colors([
                        'primary',
                    ]),

                Tables\Columns\TextColumn::make('city')
                    ->searchable(),

                Tables\Columns\TextColumn::make('locality')
                    ->searchable(),

                Tables\Columns\TextColumn::make('rent_per_month')
                    ->label('Rent')
                    ->money('INR'),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),

                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_broker')
                    ->boolean(),

                Tables\Columns\TextColumn::make('view_count')
                    ->label('Views'),

                Tables\Columns\TextColumn::make('unlock_count')
                    ->label('Unlocks'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y'),
            ])
            ->filters([

                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),

                SelectFilter::make('property_type')
                    ->options([
                        'room' => 'Room',
                        'shared_room' => 'Shared Room',
                        'flat' => 'Flat',
                        'house' => 'House',
                        'pg' => 'PG',
                        'office' => 'Office',
                        'shop' => 'Shop',
                        'warehouse' => 'Warehouse',
                    ]),

                SelectFilter::make('city')
                    ->options(
                        Listing::query()
                            ->distinct()
                            ->pluck('city', 'city')
                            ->toArray()
                    ),

            ])
            ->actions([

                Tables\Actions\Action::make('approve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->action(fn (Listing $record) => $record->update([
                        'status' => 'approved',
                    ]))
                    ->visible(fn (Listing $record) => $record->status !== 'approved'),

                Tables\Actions\Action::make('reject')
                    ->icon('heroicon-o-x')
                    ->color('danger')
                    ->action(fn (Listing $record) => $record->update([
                        'status' => 'rejected',
                    ]))
                    ->visible(fn (Listing $record) => $record->status !== 'rejected'),

                Tables\Actions\Action::make('feature')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->action(fn (Listing $record) => $record->update([
                        'is_featured' => ! $record->is_featured,
                    ])),

                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([

                Tables\Actions\DeleteBulkAction::make(),

                Tables\Actions\BulkAction::make('approve_selected')
                    ->action(fn ($records) => $records->each->update([
                        'status' => 'approved',
                    ])),

                Tables\Actions\BulkAction::make('reject_selected')
                    ->action(fn ($records) => $records->each->update([
                        'status' => 'rejected',
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
            'index' => Pages\ListListings::route('/'),
            'create' => Pages\CreateListing::route('/create'),
            'edit' => Pages\EditListing::route('/{record}/edit'),
        ];
    }
}