<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\AuditLog;
use App\Models\Order;
use App\Models\Dispute;
use App\Services\EscrowService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'P2P Exchange';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->title('Order Details')
                    ->schema([
                        Forms\Components\TextInput::make('id')
                            ->label('Order ID')
                            ->disabled(),
                        Forms\Components\Select::make('buyer_id')
                            ->relationship('buyer', 'name')
                            ->disabled(),
                        Forms\Components\Select::make('seller_id')
                            ->relationship('seller', 'name')
                            ->disabled(),
                        Forms\Components\TextInput::make('amount_usdt')
                            ->label('USDT Amount')
                            ->disabled(),
                        Forms\Components\TextInput::make('amount_fiat')
                            ->label('Fiat Amount (PKR)')
                            ->disabled(),
                        Forms\Components\TextInput::make('rate')
                            ->label('Exchange Rate')
                            ->disabled(),
                        Forms\Components\TextInput::make('status')
                            ->disabled(),
                    ])->columns(2),

                Forms\Components\Card::make()
                    ->title('Payment Verification')
                    ->schema([
                        Forms\Components\Placeholder::make('payment_screenshot')
                            ->label('Payment Screenshot')
                            ->content(fn ($record) => $record && $record->payment_screenshot
                                ? new HtmlString("<a href='" . asset('storage/' . $record->payment_screenshot) . "' target='_blank'><img src='" . asset('storage/' . $record->payment_screenshot) . "' style='max-height: 300px; border-radius: 8px;' /></a>")
                                : 'No screenshot uploaded'
                            ),
                    ]),

                Forms\Components\Card::make()
                    ->title('Dispute Information')
                    ->schema([
                        Forms\Components\Placeholder::make('dispute_status')
                            ->label('Disputed')
                            ->content(fn ($record) => $record && $record->dispute
                                ? new HtmlString("<span class='text-danger fw-bold'>Dispute opened by: " . $record->dispute->user->name . "</span>")
                                : 'No active dispute'
                            ),
                        Forms\Components\Placeholder::make('dispute_reason')
                            ->label('Dispute Reason')
                            ->content(fn ($record) => $record && $record->dispute
                                ? $record->dispute->reason
                                : 'N/A'
                            ),
                        Forms\Components\Placeholder::make('dispute_resolution')
                            ->label('Resolution Details')
                            ->content(fn ($record) => $record && $record->dispute && $record->dispute->resolved_at
                                ? "Resolved by Admin ID: {$record->dispute->resolved_by} on {$record->dispute->resolved_at} (Result: " . strtoupper($record->dispute->resolution) . ")"
                                : 'Unresolved / Open'
                            ),
                    ])->visible(fn ($record) => $record && $record->dispute !== null)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Order ID')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('buyer.name')
                    ->label('Buyer')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('seller.name')
                    ->label('Seller')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount_usdt')
                    ->label('Amount USDT')
                    ->numeric(decimalPlaces: 8)
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount_fiat')
                    ->label('Amount Fiat')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'pending' => 'warning',
                        'paid' => 'info',
                        'disputed' => 'danger',
                        'cancelled' => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('expiry_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'completed' => 'Completed',
                        'disputed' => 'Disputed',
                        'cancelled' => 'Cancelled',
                    ])
            ])
            ->actions([
                Tables\Actions\Action::make('resolve_release')
                    ->label('Release to Buyer')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => in_array($record->status, ['disputed', 'paid', 'pending']) && $record->escrow && $record->escrow->status === 'locked')
                    ->action(function ($record) {
                        $escrowService = new EscrowService();
                        $escrowService->release($record);

                        $record->update([
                            'status' => 'completed',
                            'completed_at' => now(),
                        ]);

                        if ($record->dispute) {
                            $record->dispute->update([
                                'resolved_by' => auth()->id(),
                                'resolved_at' => now(),
                                'resolution' => 'release_to_buyer',
                            ]);
                        }

                        AuditLog::create([
                            'user_id' => $record->buyer_id,
                            'action' => 'DISPUTE_RESOLVED_RELEASE',
                            'description' => "Admin resolved dispute. Escrow released to buyer for Order ID: {$record->id}",
                            'ip_address' => request()->ip() ?? '127.0.0.1',
                        ]);
                    }),

                Tables\Actions\Action::make('resolve_refund')
                    ->label('Refund to Seller')
                    ->color('danger')
                    ->icon('heroicon-o-arrow-path')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => in_array($record->status, ['disputed', 'paid', 'pending']) && $record->escrow && $record->escrow->status === 'locked')
                    ->action(function ($record) {
                        $escrowService = new EscrowService();
                        $escrowService->refund($record);

                        $record->update([
                            'status' => 'cancelled',
                            'cancelled_at' => now(),
                        ]);

                        if ($record->dispute) {
                            $record->dispute->update([
                                'resolved_by' => auth()->id(),
                                'resolved_at' => now(),
                                'resolution' => 'refund_to_seller',
                            ]);
                        }

                        AuditLog::create([
                            'user_id' => $record->seller_id,
                            'action' => 'DISPUTE_RESOLVED_REFUND',
                            'description' => "Admin resolved dispute. Escrow refunded to seller for Order ID: {$record->id}",
                            'ip_address' => request()->ip() ?? '127.0.0.1',
                        ]);
                    }),

                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
