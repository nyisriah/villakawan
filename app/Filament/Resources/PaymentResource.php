<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use App\Models\Booking;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Select::make('booking_id')
                    ->required()
                    ->relationship(
                        'booking',
                        'id',
                        fn (Builder $query) => $query->with('user', 'villa')
                    )
                    ->searchable()
                    ->preload()
                    ->getOptionLabelsUsing(fn (array $values) => 
                        Booking::whereIn('id', $values)
                            ->with('user', 'villa')
                            ->get()
                            ->mapWithKeys(fn ($booking) => [
                                $booking->id => "Booking #{$booking->id} - {$booking->user->name} ({$booking->villa->name})"
                            ])
                            ->toArray()
                    )
                    ->label('Booking')
                    ->disabled(fn (string $operation) => $operation === 'edit'),

                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->minValue(0.01)
                    ->step(0.01)
                    ->prefix('Rp ')
                    ->label('Amount')
                    ->disabled(fn (string $operation) => $operation === 'edit'),

                // ✅ MANUAL PAYMENT
                Forms\Components\Select::make('payment_method')
                    ->required()
                    ->options([
                        'bank_transfer' => 'Bank Transfer',
                        'e_wallet' => 'E-Wallet',
                    ])
                    ->default('bank_transfer')
                    ->label('Payment Method')
                    ->disabled(fn (string $operation) => $operation === 'edit'),

                // ✅ OPTIONAL (TIDAK WAJIB LAGI)
                Forms\Components\TextInput::make('doku_transaction_id')
                    ->nullable()
                    ->maxLength(255)
                    ->label('Transaction ID (Optional)')
                    ->disabled(fn (string $operation) => $operation === 'edit'),

                Forms\Components\Select::make('status')
                    ->required()
                    ->options([
                        'pending' => 'Pending',
                        'success' => 'Success',
                        'failed' => 'Failed',
                    ])
                    ->default('pending')
                    ->label('Status'),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('booking_id')
                    ->formatStateUsing(fn ($state) => "#{$state}")
                    ->label('Booking ID'),
                Tables\Columns\ImageColumn::make('proof')
                    ->disk('public')
                    ->label('Bukti Bayar'),
                Tables\Columns\TextColumn::make('booking.user.name')
                    ->label('Customer'),

                Tables\Columns\TextColumn::make('booking.villa.name')
                    ->label('Villa'),

                Tables\Columns\TextColumn::make('amount')
                    ->money('IDR')
                    ->label('Amount'),

                Tables\Columns\TextColumn::make('payment_method')
                    ->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state)))
                    ->label('Method'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'pending' => 'warning',
                        'success' => 'success',
                        'failed' => 'danger',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y H:i')
                    ->label('Created At'),

            ])
            ->filters([

                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'success' => 'Success',
                        'failed' => 'Failed',
                    ]),

                SelectFilter::make('payment_method')
                    ->options([
                        'bank_transfer' => 'Bank Transfer',
                        'e_wallet' => 'E-Wallet',
                    ]),

            ])
            ->actions([

                // ✅ APPROVE PAYMENT
                Tables\Actions\Action::make('confirm')
                    ->label('Confirm Payment')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->visible(fn (Payment $record) => $record->status === 'pending' && $record->booking->status === 'paid')
                    ->requiresConfirmation()
                    ->action(function (Payment $record) {
                        // 🔐 SECURITY: Validate payment status & booking status
                        if ($record->status !== 'pending') {
                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('Error')
                                ->body('Payment bukan pending status')
                                ->send();
                            return;
                        }

                        if ($record->booking->status !== 'paid') {
                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('Error')
                                ->body('Booking status bukan paid')
                                ->send();
                            return;
                        }

                        // 🔐 SECURITY: Validate booking can transition to confirmed
                        if (!$record->booking->canTransitionTo('confirmed')) {
                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('Error')
                                ->body('Status transition tidak valid')
                                ->send();
                            return;
                        }

                        // ✅ UPDATE payment status
                        $record->update(['status' => 'success']);

                        // ✅ AUTO UPDATE booking status
                        $record->booking->update(['status' => 'confirmed']);

                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Success')
                            ->body('Payment berhasil dikonfirmasi')
                            ->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Reject Payment')
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')
                    ->visible(fn (Payment $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (Payment $record) {
                        // 🔐 SECURITY: Validate payment status
                        if ($record->status !== 'pending') {
                            return;
                        }

                        // ✅ UPDATE payment status ke failed
                        $record->update(['status' => 'failed']);

                        // ✅ REVERT booking status ke approved
                        $record->booking->update(['status' => 'approved']);

                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Success')
                            ->body('Payment ditolak, booking kembali ke status approved')
                            ->send();
                    }),

                Tables\Actions\EditAction::make()
                    ->disabled(fn (Payment $record) => $record->status !== 'pending'),

                Tables\Actions\DeleteAction::make()
                    ->disabled(fn (Payment $record) => $record->status !== 'pending'),

            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}