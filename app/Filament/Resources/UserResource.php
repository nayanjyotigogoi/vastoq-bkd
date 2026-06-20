<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Users';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([

            Grid::make(2)->schema([

                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('phone')
                    ->required()
                    ->unique(ignoreRecord: true),

                TextInput::make('email')
                    ->email()
                    ->unique(ignoreRecord: true),

                TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context): bool => $context === 'create'),

                Select::make('role')
                    ->options([
                        'tenant' => 'Tenant',
                        'owner' => 'Owner',
                        'worker' => 'Worker',
                        'admin' => 'Admin',
                    ])
                    ->required(),

                TextInput::make('credit_balance')
                    ->numeric()
                    ->default(0)
                    ->required(),

                Toggle::make('is_verified'),

                Toggle::make('is_blocked'),

                TextInput::make('profile_photo_url')
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

                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),

                Tables\Columns\TextColumn::make('email')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('role')
                    ->colors([
                        'primary' => 'tenant',
                        'success' => 'owner',
                        'warning' => 'worker',
                        'danger' => 'admin',
                    ]),

                Tables\Columns\TextColumn::make('credit_balance')
                    ->label('Credits'),

                Tables\Columns\IconColumn::make('is_verified')
                    ->boolean()
                    ->label('Verified'),

                Tables\Columns\IconColumn::make('is_blocked')
                    ->boolean()
                    ->label('Blocked'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y'),
            ])
            ->filters([

                SelectFilter::make('role')
                    ->options([
                        'tenant' => 'Tenant',
                        'owner' => 'Owner',
                        'worker' => 'Worker',
                        'admin' => 'Admin',
                    ]),

                SelectFilter::make('is_verified')
                    ->options([
                        1 => 'Verified',
                        0 => 'Not Verified',
                    ]),

                SelectFilter::make('is_blocked')
                    ->options([
                        1 => 'Blocked',
                        0 => 'Active',
                    ]),
            ])
            ->actions([

                Tables\Actions\Action::make('verify')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->action(fn (User $record) => $record->update([
                        'is_verified' => true,
                    ]))
                    ->visible(fn (User $record) => !$record->is_verified),

                Tables\Actions\Action::make('block')
                    ->color('danger')
                    ->icon('heroicon-o-ban')
                    ->action(fn (User $record) => $record->update([
                        'is_blocked' => true,
                    ]))
                    ->visible(fn (User $record) => !$record->is_blocked),

                Tables\Actions\Action::make('unblock')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->action(fn (User $record) => $record->update([
                        'is_blocked' => false,
                    ]))
                    ->visible(fn (User $record) => $record->is_blocked),

                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([

                Tables\Actions\DeleteBulkAction::make(),

                Tables\Actions\BulkAction::make('verify_selected')
                    ->action(fn ($records) => $records->each->update([
                        'is_verified' => true,
                    ])),

                Tables\Actions\BulkAction::make('block_selected')
                    ->action(fn ($records) => $records->each->update([
                        'is_blocked' => true,
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}