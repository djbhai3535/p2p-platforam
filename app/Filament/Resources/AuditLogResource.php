<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditLogResource\Pages;
use App\Models\AuditLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-magnifying-glass';

    protected static ?string $navigationGroup = 'System Settings';

    protected static ?string $navigationLabel = 'Audit Logs';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->disabled(),
                        Forms\Components\TextInput::make('action')
                            ->disabled(),
                        Forms\Components\TextInput::make('ip_address')
                            ->disabled()
                            ->label('IP Address'),
                        Forms\Components\Textarea::make('description')
                            ->disabled()
                            ->columnSpanFull(),
                        Forms\Components\DateTimePicker::make('created_at')
                            ->disabled()
                            ->label('Timestamp'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Timestamp')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('action')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'LOGIN' => 'gray',
                        '2FA_ENABLE', '2FA_DISABLE' => 'primary',
                        'KYC_SUBMIT', 'KYC_APPROVE', 'KYC_REJECT' => 'info',
                        'ESCROW_LOCK', 'ESCROW_RELEASE', 'ESCROW_REFUND' => 'success',
                        'DISPUTE_RESOLVED_RELEASE', 'DISPUTE_RESOLVED_REFUND' => 'danger',
                        default => 'warning',
                    })
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->limit(60)
                    ->searchable(),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('action')
                    ->options([
                        'LOGIN' => 'User Logins',
                        'KYC_SUBMIT' => 'KYC Submissions',
                        'KYC_APPROVE' => 'KYC Approvals',
                        'KYC_REJECT' => 'KYC Rejections',
                        'ESCROW_LOCK' => 'Escrow Locks',
                        'ESCROW_RELEASE' => 'Escrow Releases',
                        'ESCROW_REFUND' => 'Escrow Refunds',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // Read-only: no bulk deletion allowed
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        // Only allow listing and viewing details to keep logs untamperable
        return [
            'index' => Pages\ListAuditLogs::route('/'),
        ];
    }
}
