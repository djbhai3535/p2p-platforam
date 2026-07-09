<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentMethodResource\Pages;
use App\Models\Country;
use App\Models\PaymentMethod;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PaymentMethodResource extends Resource
{
    protected static ?string $model = PaymentMethod::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-bank';

    protected static ?string $navigationGroup = 'Financial Management';

    protected static ?string $navigationLabel = 'Payment Methods';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Select::make('country_id')
                            ->label('Country')
                            ->options(Country::all()->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->maxLength(255),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Is Active')
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Card::make()
                    ->title('Configurable User Fields')
                    ->description('Define the fields that sellers must fill in when linking their accounts (e.g. Account Number, IBAN).')
                    ->schema([
                        Forms\Components\Repeater::make('fields')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->placeholder('e.g. account_number')
                                    ->label('Field Name / Key'),
                                Forms\Components\TextInput::make('label')
                                    ->required()
                                    ->placeholder('e.g. Account Number')
                                    ->label('Input Label (Displayed to User)'),
                                Forms\Components\Select::make('type')
                                    ->options([
                                        'text' => 'Text Field',
                                        'number' => 'Number Field',
                                    ])
                                    ->required(),
                                Forms\Components\Toggle::make('required')
                                    ->label('Required')
                                    ->default(true),
                            ])
                            ->columns(4)
                            ->default([]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('country.name')
                    ->label('Country')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('country')
                    ->relationship('country', 'name'),
                Tables\Filters\TernaryFilter::make('is_active')->label('Is Active'),
            ])
            ->actions([
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
            'index' => Pages\ListPaymentMethods::route('/'),
            'create' => Pages\CreatePaymentMethod::route('/create'),
            'edit' => Pages\EditPaymentMethod::route('/{record}/edit'),
        ];
    }
}
