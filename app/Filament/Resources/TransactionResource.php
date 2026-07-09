<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Transaction;
use App\Services\WalletService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationGroup = 'Financial Management';

    protected static ?string $navigationLabel = 'Deposits & Withdrawals';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Select::make('wallet_id')
                            ->relationship('wallet.user', 'name')
                            ->disabled()
                            ->required(),
                        Forms\Components\TextInput::make('type')
                            ->disabled()
                            ->required(),
                        Forms\Components\TextInput::make('amount')
                            ->disabled()
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('fee')
                            ->disabled()
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('status')
                            ->disabled()
                            ->required(),
                        Forms\Components\TextInput::make('address')
                            ->disabled()
                            ->label('Destination Address'),
                        Forms\Components\TextInput::make('txid')
                            ->disabled()
                            ->label('Transaction ID (TXID)'),
                        Forms\Components\TextInput::make('payment_provider')
                            ->disabled()
                            ->label('Payment Provider'),
                        Forms\Components\TextInput::make('payment_id')
                            ->disabled()
                            ->label('External Payment ID'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('wallet.user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'deposit' => 'success',
                        'withdrawal' => 'warning',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->numeric(decimalPlaces: 8)
                    ->sortable(),
                Tables\Columns\TextColumn::make('fee')
                    ->numeric(decimalPlaces: 8)
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'pending' => 'warning',
                        'processing' => 'info',
                        'rejected' => 'danger',
                        'cancelled' => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('address')
                    ->limit(20)
                    ->searchable(),
                Tables\Columns\TextColumn::make('txid')
                    ->label('TXID')
                    ->limit(20)
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'deposit' => 'Deposits',
                        'withdrawal' => 'Withdrawals',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'rejected' => 'Rejected',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->type === 'withdrawal' && $record->status === 'pending')
                    ->action(function ($record) {
                        $walletService = new WalletService;
                        $walletService->approveWithdrawal($record);
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->type === 'withdrawal' && $record->status === 'pending')
                    ->action(function ($record) {
                        $walletService = new WalletService;
                        $walletService->rejectWithdrawal($record);
                    }),

                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
        ];
    }
}
