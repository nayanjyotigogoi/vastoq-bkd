<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkerResource\Pages;
use App\Models\Worker;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\HtmlString;

class WorkerResource extends Resource
{
    protected static ?string $model = Worker::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationLabel = 'Workers';

    protected static ?string $navigationGroup = 'Worker Management';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $pending = Worker::where('aadhaar_status', 'pending')->count();
        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([

            Grid::make(2)->schema([

                TextInput::make('user.name')
                    ->label('Name')
                    ->disabled(),

                TextInput::make('user.phone')
                    ->label('Phone')
                    ->disabled(),

                TextInput::make('category')
                    ->required(),

                TextInput::make('city')
                    ->required(),

                TextInput::make('locality'),

                TextInput::make('rate_per_day')
                    ->numeric()
                    ->label('Rate / day (₹)'),

                Toggle::make('is_verified')
                    ->label('Verified badge')
                    ->disabled()
                    ->helperText('Controlled by the Aadhaar status below.'),

                Toggle::make('is_active'),

                Select::make('aadhaar_status')
                    ->options([
                        'unverified' => 'Unverified',
                        'pending'    => 'Pending review',
                        'verified'   => 'Verified',
                        'rejected'   => 'Rejected',
                    ])
                    ->disabled()
                    ->columnSpan(2),

                TextInput::make('aadhaar_number')
                    ->label('Aadhaar number')
                    ->disabled()
                    ->columnSpan(2),

                Textarea::make('aadhaar_rejection_reason')
                    ->label('Rejection reason')
                    ->rows(2)
                    ->columnSpan(2),

                Placeholder::make('aadhaar_front_preview')
                    ->label('Aadhaar — front')
                    ->content(fn (?Worker $record) => $record?->aadhaar_front_url
                        ? new HtmlString('<a href="'.$record->aadhaar_front_url.'" target="_blank"><img src="'.$record->aadhaar_front_url.'" style="max-width:260px;border-radius:8px;border:1px solid #e5e0d5" /></a>')
                        : new HtmlString('<span style="color:#8A8480">Not submitted</span>')),

                Placeholder::make('aadhaar_back_preview')
                    ->label('Aadhaar — back')
                    ->content(fn (?Worker $record) => $record?->aadhaar_back_url
                        ? new HtmlString('<a href="'.$record->aadhaar_back_url.'" target="_blank"><img src="'.$record->aadhaar_back_url.'" style="max-width:260px;border-radius:8px;border:1px solid #e5e0d5" /></a>')
                        : new HtmlString('<span style="color:#8A8480">Not submitted</span>')),

                Textarea::make('bio')
                    ->rows(3)
                    ->columnSpan(2),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('aadhaar_submitted_at', 'desc')
            ->columns([

                Tables\Columns\TextColumn::make('id')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.phone')
                    ->label('Phone')
                    ->searchable(),

                Tables\Columns\TextColumn::make('category')
                    ->searchable(),

                Tables\Columns\TextColumn::make('city')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('aadhaar_status')
                    ->label('Aadhaar')
                    ->colors([
                        'secondary' => 'unverified',
                        'warning'   => 'pending',
                        'success'   => 'verified',
                        'danger'    => 'rejected',
                    ]),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('aadhaar_submitted_at')
                    ->label('Submitted')
                    ->dateTime('d M Y, h:i A')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y'),
            ])
            ->filters([

                SelectFilter::make('aadhaar_status')
                    ->options([
                        'unverified' => 'Unverified',
                        'pending'    => 'Pending review',
                        'verified'   => 'Verified',
                        'rejected'   => 'Rejected',
                    ]),

                SelectFilter::make('category')
                    ->options(
                        Worker::query()
                            ->distinct()
                            ->pluck('category', 'category')
                            ->toArray()
                    ),

                SelectFilter::make('city')
                    ->options(
                        Worker::query()
                            ->distinct()
                            ->pluck('city', 'city')
                            ->toArray()
                    ),
            ])
            ->actions([

                Tables\Actions\Action::make('verify')
                    ->icon('heroicon-o-badge-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (Worker $record) => $record->update([
                        'is_verified'              => true,
                        'aadhaar_status'           => 'verified',
                        'aadhaar_rejection_reason' => null,
                    ]))
                    ->visible(fn (Worker $record) => $record->aadhaar_status !== 'verified'),

                Tables\Actions\Action::make('reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Textarea::make('reason')
                            ->label('Reason for rejection')
                            ->required()
                            ->rows(2),
                    ])
                    ->action(fn (Worker $record, array $data) => $record->update([
                        'is_verified'              => false,
                        'aadhaar_status'           => 'rejected',
                        'aadhaar_rejection_reason' => $data['reason'],
                    ]))
                    ->visible(fn (Worker $record) => $record->aadhaar_status !== 'rejected'),

                Tables\Actions\Action::make('toggle_active')
                    ->label(fn (Worker $record) => $record->is_active ? 'Deactivate' : 'Activate')
                    ->icon('heroicon-o-lightning-bolt')
                    ->color('warning')
                    ->action(fn (Worker $record) => $record->update(['is_active' => !$record->is_active])),

                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListWorkers::route('/'),
            'edit'  => Pages\EditWorker::route('/{record}/edit'),
        ];
    }
}
