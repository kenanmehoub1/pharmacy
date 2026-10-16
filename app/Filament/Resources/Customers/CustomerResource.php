<?php

namespace App\Filament\Resources\Customers;

use App\Filament\Resources\Customers\Pages\CreateCustomer;
use App\Filament\Resources\Customers\Pages\EditCustomer;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Models\Customer;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 8;

    public static function getNavigationLabel(): string
    {
        return __('navigation.customers');
    }

    public static function getModelLabel(): string
    {
        return __('customer.title');
    }

    public static function getPluralModelLabel(): string
    {
        return __('customer.title_plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('customer.name'))
                    ->required()
                    ->maxLength(255),

                TextInput::make('phone')
                    ->label(__('customer.phone'))
                    ->tel()
                    ->maxLength(255),

                TextInput::make('email')
                    ->label(__('customer.email'))
                    ->email()
                    ->maxLength(255),

                TextInput::make('balance')
                    ->label(__('customer.balance'))
                    ->numeric()
                    ->default(0)
                    ->prefix('EGP'),

                Textarea::make('address')
                    ->label(__('customer.address'))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('customer.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone')
                    ->label(__('customer.phone'))
                    ->searchable(),

                TextColumn::make('email')
                    ->label(__('customer.email'))
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('balance')
                    ->label(__('customer.balance'))
                    ->money('EGP')
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'success')
                    ->sortable(),

                TextColumn::make('created_at')
    ->label(__('customer.created_at'))
    ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->format('d/m/Y') : '-')
    ->sortable()
    ->toggleable(isToggledHiddenByDefault: true),

TextColumn::make('updated_at')
    ->label(__('customer.updated_at'))
    ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->format('d/m/Y') : '-')
    ->sortable()
    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomers::route('/'),
            'create' => CreateCustomer::route('/create'),
            'edit' => EditCustomer::route('/{record}/edit'),
        ];
    }
}