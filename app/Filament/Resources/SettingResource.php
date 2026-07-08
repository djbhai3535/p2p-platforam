<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Models\Setting;
use App\Services\SettingsService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'System Settings';

    protected static ?string $navigationLabel = 'Platform Settings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\TextInput::make('key')
                            ->required()
                            ->maxLength(255)
                            ->disabled(fn ($record) => $record !== null), // Disable key edits for safety
                        Forms\Components\TextInput::make('value')
                            ->maxLength(65535),
                        Forms\Components\Select::make('group')
                            ->options([
                                'general' => 'General Settings',
                                'fees' => 'Fee Configuration',
                                'nowpayments' => 'NOWPayments Credentials',
                                'theme' => 'Theme Configurations',
                                'language' => 'Locales / Languages',
                            ])
                            ->required(),
                    ])->columns(1)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('value')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('group')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'general' => 'gray',
                        'fees' => 'success',
                        'nowpayments' => 'warning',
                        'theme' => 'info',
                        'language' => 'primary',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('group')
                    ->options([
                        'general' => 'General Settings',
                        'fees' => 'Fee Configuration',
                        'nowpayments' => 'NOWPayments Credentials',
                        'theme' => 'Theme Configurations',
                        'language' => 'Locales / Languages',
                    ])
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(function ($record) {
                        // Clear settings cache on save
                        SettingsService::clearCache();
                    }),
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
            'index' => Pages\ListSettings::route('/'),
            'create' => Pages\CreateSetting::route('/create'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }
}
