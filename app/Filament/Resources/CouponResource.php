<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages;
use App\Models\Coupon;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationLabel = 'Coupons';

    protected static ?string $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([

            Grid::make(2)->schema([

                TextInput::make('code')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(100),

                Select::make('type')
                    ->options([
                        'flat' => 'Flat Discount',
                        'percent' => 'Percentage Discount',
                        'free_unlock' => 'Free Unlock',
                    ])
                    ->required(),

                TextInput::make('value')
                    ->numeric()
                    ->required()
                    ->default(0),

                TextInput::make('usage_limit')
                    ->numeric()
                    ->nullable(),

                TextInput::make('used_count')
                    ->numeric()
                    ->disabled()
                    ->default(0),

                Toggle::make('is_active')
                    ->default(true),

                DateTimePicker::make('starts_at'),

                DateTimePicker::make('expires_at'),
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

                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'success' => 'flat',
                        'warning' => 'percent',
                        'primary' => 'free_unlock',
                    ]),

                Tables\Columns\TextColumn::make('value'),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('usage_limit')
                    ->label('Limit'),

                Tables\Columns\TextColumn::make('used_count')
                    ->label('Used'),

                Tables\Columns\TextColumn::make('starts_at')
                    ->dateTime('d M Y'),

                Tables\Columns\TextColumn::make('expires_at')
                    ->dateTime('d M Y'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y'),
            ])
            ->filters([

                SelectFilter::make('type')
                    ->options([
                        'flat' => 'Flat',
                        'percent' => 'Percent',
                        'free_unlock' => 'Free Unlock',
                    ]),

                SelectFilter::make('is_active')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ]),
            ])
            ->actions([

                Tables\Actions\Action::make('activate')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->action(fn (Coupon $record) => $record->update([
                        'is_active' => true,
                    ]))
                    ->visible(fn (Coupon $record) => !$record->is_active),

                Tables\Actions\Action::make('deactivate')
                    ->color('danger')
                    ->icon('heroicon-o-x')
                    ->action(fn (Coupon $record) => $record->update([
                        'is_active' => false,
                    ]))
                    ->visible(fn (Coupon $record) => $record->is_active),

                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([

                Tables\Actions\DeleteBulkAction::make(),

                Tables\Actions\BulkAction::make('activate_selected')
                    ->action(fn ($records) => $records->each->update([
                        'is_active' => true,
                    ])),

                Tables\Actions\BulkAction::make('deactivate_selected')
                    ->action(fn ($records) => $records->each->update([
                        'is_active' => false,
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
            'index' => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'edit' => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}