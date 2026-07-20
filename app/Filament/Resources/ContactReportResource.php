<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactReportResource\Pages;
use App\Mail\ReportRefundedMail;
use App\Mail\ReportRejectedMail;
use App\Models\ContactReport;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactReportResource extends Resource
{
    protected static ?string $model = ContactReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-flag';

    protected static ?string $navigationLabel = 'Contact Reports';

    protected static ?string $navigationGroup = 'Disputes & Refunds';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'id';

    /* ------------------------------------------------------------------ */
    /* Form                                                               */
    /* ------------------------------------------------------------------ */

    public static function form(Form $form): Form
    {
        return $form->schema([
            Card::make()->schema([
                Grid::make(2)->schema([
                    TextInput::make('id')
                        ->label('Report ID')
                        ->disabled(),

                    TextInput::make('status')
                        ->label('Status')
                        ->formatStateUsing(fn ($state) => ucfirst($state ?? 'pending'))
                        ->disabled(),

                    TextInput::make('reporter_name')
                        ->label('Reporter Name')
                        ->getStateUsing(fn (ContactReport $record) => $record->user?->name ?? '—')
                        ->disabled(),

                    TextInput::make('reporter_email')
                        ->label('Reporter Email')
                        ->getStateUsing(fn (ContactReport $record) => $record->user?->email ?? '—')
                        ->disabled(),

                    TextInput::make('reporter_phone')
                        ->label('Reporter Phone (Callback)')
                        ->getStateUsing(fn (ContactReport $record) => $record->reporter_phone ?? $record->user?->phone ?? '—')
                        ->disabled(),

                    TextInput::make('reportable_type')
                        ->label('Report Type')
                        ->formatStateUsing(fn ($state) => ucfirst($state ?? '—'))
                        ->disabled(),

                    TextInput::make('subject_title')
                        ->label('Property / Worker Title')
                        ->getStateUsing(function (ContactReport $record): string {
                            if ($record->isListing() && $record->listing) {
                                return $record->listing->title ?? '—';
                            }
                            if ($record->isWorker() && $record->worker) {
                                return ($record->worker->user?->name ?? '—') . ' (' . ($record->worker->category ?? '') . ')';
                            }
                            return '—';
                        })
                        ->disabled(),

                    TextInput::make('owner_phone')
                        ->label('Owner / Worker Phone')
                        ->getStateUsing(function (ContactReport $record): string {
                            if ($record->isListing() && $record->listing) {
                                return $record->listing->owner?->phone ?? '—';
                            }
                            if ($record->isWorker() && $record->worker) {
                                return $record->worker->user?->phone ?? '—';
                            }
                            return '—';
                        })
                        ->disabled(),

                    TextInput::make('reason')
                        ->label('Reason Reported')
                        ->formatStateUsing(fn ($state, ContactReport $record) => $record->reason_label)
                        ->disabled(),
                ]),

                Textarea::make('elaborated_reason')
                    ->label('Elaborated Details / Additional Explanation')
                    ->getStateUsing(fn (ContactReport $record) => filled($record->elaborated_reason) ? $record->elaborated_reason : 'No additional details provided by user.')
                    ->disabled()
                    ->columnSpanFull(),

                Textarea::make('admin_note')
                    ->label('Admin Rejection Reason / Note')
                    ->getStateUsing(fn (ContactReport $record) => filled($record->admin_note) ? $record->admin_note : 'None')
                    ->disabled()
                    ->columnSpanFull(),
            ]),
        ]);
    }

    /* ------------------------------------------------------------------ */
    /* Table                                                              */
    /* ------------------------------------------------------------------ */

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([

                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('reportable_type')
                    ->label('Type')
                    ->colors([
                        'primary' => 'listing',
                        'warning' => 'worker',
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst($state)),

                // Subject name (listing title or worker name)
                Tables\Columns\TextColumn::make('subject_name')
                    ->label('Property / Worker')
                    ->getStateUsing(function (ContactReport $record): string {
                        if ($record->isListing() && $record->listing) {
                            return $record->listing->title ?? '—';
                        }
                        if ($record->isWorker() && $record->worker) {
                            return ($record->worker->user?->name ?? '—') . ' (' . ($record->worker->category ?? '') . ')';
                        }
                        return '—';
                    })
                    ->limit(40),

                // Owner / worker phone
                Tables\Columns\TextColumn::make('owner_phone')
                    ->label('Owner / Worker Phone')
                    ->getStateUsing(function (ContactReport $record): string {
                        if ($record->isListing() && $record->listing) {
                            return $record->listing->owner?->phone ?? '—';
                        }
                        if ($record->isWorker() && $record->worker) {
                            return $record->worker->user?->phone ?? '—';
                        }
                        return '—';
                    }),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Reporter')
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('Reporter Email')
                    ->searchable(),

                Tables\Columns\TextColumn::make('reporter_phone')
                    ->label('Reporter Phone')
                    ->getStateUsing(fn (ContactReport $record) => $record->reporter_phone ?? $record->user?->phone ?? '—')
                    ->searchable(),

                Tables\Columns\TextColumn::make('reason')
                    ->label('Reason')
                    ->formatStateUsing(fn ($state, ContactReport $record) => $record->reason_label)
                    ->wrap(),

                Tables\Columns\TextColumn::make('elaborated_reason')
                    ->label('Details')
                    ->formatStateUsing(fn ($state) => filled($state) ? $state : '—')
                    ->limit(60)
                    ->wrap(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'refunded',
                        'danger'  => 'rejected',
                    ]),

                Tables\Columns\TextColumn::make('points_refunded')
                    ->label('Pts Refunded'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Reported At')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending'  => 'Pending',
                        'refunded' => 'Refunded',
                        'rejected' => 'Rejected',
                    ]),

                SelectFilter::make('reportable_type')
                    ->label('Type')
                    ->options([
                        'listing' => 'Listing',
                        'worker'  => 'Worker',
                    ]),

                SelectFilter::make('reason')
                    ->options([
                        'already_rented'  => 'Property already rented / Unavailable',
                        'invalid_details' => 'Number switched off / Invalid',
                        'extra_brokerage' => 'Extra brokerage demanded',
                        'other'           => 'Other',
                    ]),
            ])
            ->actions([

                /* ---- VIEW ---- */
                Tables\Actions\ViewAction::make(),

                /* ---- REFUND ---- */
                Action::make('refund')
                    ->label('Refund')
                    ->icon('heroicon-o-currency-rupee')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Approve Refund')
                    ->modalSubheading(fn (ContactReport $record) => "This will credit back the unlock points to {$record->user?->name} and notify them by email.")
                    ->modalButton('Yes, Refund Points')
                    ->visible(fn (ContactReport $record) => $record->status === 'pending')
                    ->action(function (ContactReport $record): void {
                        // Determine how many points to refund based on type
                        $points = $record->isListing() ? 20 : 10;

                        $record->update([
                            'status'          => 'refunded',
                            'points_refunded' => $points,
                        ]);

                        // Credit back points
                        $record->user?->increment('vastoq_points', $points);

                        // Send email
                        try {
                            if ($record->user?->email) {
                                Mail::to($record->user->email)->send(new ReportRefundedMail($record->load(['user', 'listing.owner', 'worker.user'])));
                            }
                        } catch (\Throwable $e) {
                            Log::error('[REPORT REFUND] Email failed', ['error' => $e->getMessage()]);
                        }

                        Notification::make()
                            ->title("Refunded {$points} points to {$record->user?->name}")
                            ->success()
                            ->send();
                    }),

                /* ---- REJECT ---- */
                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (ContactReport $record) => $record->status === 'pending')
                    ->form([
                        Textarea::make('admin_note')
                            ->label('Rejection Reason (sent to user in email)')
                            ->required()
                            ->minLength(10)
                            ->placeholder('Explain clearly why this report is being rejected. This message will be sent to the user via email.'),
                    ])
                    ->action(function (ContactReport $record, array $data): void {
                        $record->update([
                            'status'     => 'rejected',
                            'admin_note' => $data['admin_note'],
                        ]);

                        // Send rejection email
                        try {
                            if ($record->user?->email) {
                                Mail::to($record->user->email)->send(new ReportRejectedMail($record->load(['user', 'listing.owner', 'worker.user'])));
                            }
                        } catch (\Throwable $e) {
                            Log::error('[REPORT REJECT] Email failed', ['error' => $e->getMessage()]);
                        }

                        Notification::make()
                            ->title("Report #{$record->id} rejected. Email sent to user.")
                            ->warning()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    /* ------------------------------------------------------------------ */
    /* Pages                                                              */
    /* ------------------------------------------------------------------ */

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContactReports::route('/'),
        ];
    }
}
