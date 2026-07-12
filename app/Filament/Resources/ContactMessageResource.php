<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactMessageResource\Pages;
use App\Models\ContactMessage;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class ContactMessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;
    protected static ?string $navigationIcon = 'heroicon-o-mail';
    protected static ?string $navigationLabel = 'Contact Messages';
    protected static ?string $navigationGroup = 'Support';
    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'open')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Card::make()->schema([
                Forms\Components\TextInput::make('name')->disabled(),
                Forms\Components\TextInput::make('email')->disabled(),
                Forms\Components\TextInput::make('phone')->disabled(),
                Forms\Components\TextInput::make('subject')->disabled()->columnSpanFull(),
                Forms\Components\Select::make('type')->options([
                    'general'   => 'General',
                    'grievance' => 'Grievance',
                    'payment'   => 'Payment',
                    'listing'   => 'Listing',
                    'worker'    => 'Worker',
                ])->disabled(),
                Forms\Components\Textarea::make('message')->disabled()->columnSpanFull()->rows(4),
            ])->columns(2),

            Forms\Components\Card::make()->schema([
                Forms\Components\Select::make('status')->options([
                    'open'        => 'Open',
                    'in_progress' => 'In Progress',
                    'resolved'    => 'Resolved',
                ])->required(),
                Forms\Components\Textarea::make('admin_reply')
                    ->label('Admin Reply / Notes')
                    ->rows(4)
                    ->columnSpanFull(),
            ])->columns(1)->label('Admin Response'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('subject')->limit(40)->searchable(),
                Tables\Columns\BadgeColumn::make('type')->colors([
                    'secondary' => 'general',
                    'danger'    => 'grievance',
                    'warning'   => 'payment',
                    'primary'   => 'listing',
                    'success'   => 'worker',
                ]),
                Tables\Columns\BadgeColumn::make('status')->colors([
                    'danger'   => 'open',
                    'warning'  => 'in_progress',
                    'success'  => 'resolved',
                ]),
                Tables\Columns\TextColumn::make('created_at')->dateTime('d M Y, H:i')->sortable()->label('Received'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'open'        => 'Open',
                    'in_progress' => 'In Progress',
                    'resolved'    => 'Resolved',
                ]),
                Tables\Filters\SelectFilter::make('type')->options([
                    'general'   => 'General',
                    'grievance' => 'Grievance',
                    'payment'   => 'Payment',
                    'listing'   => 'Listing',
                    'worker'    => 'Worker',
                ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Review & Reply'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListContactMessages::route('/'),
            'edit'   => Pages\EditContactMessage::route('/{record}/edit'),
        ];
    }
}
