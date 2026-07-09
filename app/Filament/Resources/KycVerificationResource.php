<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KycVerificationResource\Pages;
use App\Models\AuditLog;
use App\Models\KycVerification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class KycVerificationResource extends Resource
{
    protected static ?string $model = KycVerification::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?string $navigationLabel = 'KYC Verifications';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->disabled()
                            ->required(),
                        Forms\Components\TextInput::make('full_name')
                            ->disabled(),
                        Forms\Components\DatePicker::make('dob')
                            ->label('Date of Birth')
                            ->disabled(),
                        Forms\Components\Select::make('country_id')
                            ->relationship('country', 'name')
                            ->disabled(),
                        Forms\Components\TextInput::make('document_type')
                            ->disabled(),
                        Forms\Components\TextInput::make('document_number')
                            ->disabled(),
                    ])->columns(2),

                Forms\Components\Card::make()
                    ->title('Document Previews')
                    ->schema([
                        Forms\Components\Placeholder::make('front_image')
                            ->label('Front Image')
                            ->content(fn ($record) => $record && $record->front_image_path
                                ? new HtmlString("<a href='".route('profile.kyc.document', [$record->id, 'front'])."' target='_blank'><img src='".route('profile.kyc.document', [$record->id, 'front'])."' style='max-height: 200px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1);' /></a>")
                                : 'No image uploaded'
                            ),
                        Forms\Components\Placeholder::make('back_image')
                            ->label('Back Image')
                            ->content(fn ($record) => $record && $record->back_image_path
                                ? new HtmlString("<a href='".route('profile.kyc.document', [$record->id, 'back'])."' target='_blank'><img src='".route('profile.kyc.document', [$record->id, 'back'])."' style='max-height: 200px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1);' /></a>")
                                : 'No back image required/uploaded'
                            ),
                        Forms\Components\Placeholder::make('selfie_image')
                            ->label('Selfie Verification')
                            ->content(fn ($record) => $record && $record->selfie_image_path
                                ? new HtmlString("<a href='".route('profile.kyc.document', [$record->id, 'selfie'])."' target='_blank'><img src='".route('profile.kyc.document', [$record->id, 'selfie'])."' style='max-height: 200px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1);' /></a>")
                                : 'No image uploaded'
                            ),
                    ])->columns(3),

                Forms\Components\Card::make()
                    ->title('Review Action')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->required(),
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->visible(fn ($get) => $get('status') === 'rejected')
                            ->placeholder('Describe why this document is rejected...'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Submitted Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('document_type')
                    ->label('Doc Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'id_card' => 'info',
                        'passport' => 'success',
                    }),
                Tables\Columns\TextColumn::make('document_number')
                    ->label('Doc Number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted At')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reviewed_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'approved',
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);

                        AuditLog::create([
                            'user_id' => $record->user_id,
                            'action' => 'KYC_APPROVE',
                            'description' => 'Approved KYC documents. Verified by Admin ID: '.auth()->id(),
                            'ip_address' => request()->ip() ?? '127.0.0.1',
                        ]);
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Rejection Reason')
                            ->required()
                            ->placeholder('Document image was not readable / expired...'),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'rejection_reason' => $data['reason'],
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);

                        AuditLog::create([
                            'user_id' => $record->user_id,
                            'action' => 'KYC_REJECT',
                            'description' => "Rejected KYC documents. Reason: {$data['reason']}. Verified by Admin ID: ".auth()->id(),
                            'ip_address' => request()->ip() ?? '127.0.0.1',
                        ]);
                    }),

                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKycVerifications::route('/'),
            'create' => Pages\CreateKycVerification::route('/create'),
            'edit' => Pages\EditKycVerification::route('/{record}/edit'),
        ];
    }
}
