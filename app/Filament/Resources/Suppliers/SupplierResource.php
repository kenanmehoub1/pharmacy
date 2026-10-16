<?php

namespace App\Filament\Resources\Suppliers;

use App\Filament\Resources\Suppliers\Pages\CreateSupplier;
use App\Filament\Resources\Suppliers\Pages\EditSupplier;
use App\Filament\Resources\Suppliers\Pages\ListSuppliers;
use App\Models\Setting;
use App\Models\Supplier;
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

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-truck';

    protected static ?int $navigationSort = 9;

    public static function getNavigationLabel(): string
    {
        return __('navigation.suppliers');
    }

    public static function getModelLabel(): string
    {
        return __('supplier.title');
    }

    public static function getPluralModelLabel(): string
    {
        return __('supplier.title_plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('supplier.name'))
                    ->required()
                    ->maxLength(255),

                TextInput::make('phone')
                    ->label(__('supplier.phone'))
                    ->tel()
                    ->maxLength(255),

                TextInput::make('email')
                    ->label(__('supplier.email'))
                    ->email()
                    ->maxLength(255),

                TextInput::make('balance')
                    ->label(__('supplier.balance'))
                    ->numeric()
                    ->default(0)
                    ->prefix(fn () => Setting::get('currency', 'EGP')),

                Textarea::make('address')
                    ->label(__('supplier.address'))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('supplier.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone')
                    ->label(__('supplier.phone'))
                    ->searchable(),

                TextColumn::make('email')
                    ->label(__('supplier.email'))
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('balance')
                    ->label(__('supplier.balance'))
                    ->money(fn () => Setting::get('currency', 'EGP'))
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'success')
                    ->sortable(),

                TextColumn::make('created_at')
    ->label(__('supplier.created_at'))
    ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->format('d/m/Y') : '-')
    ->sortable()
    ->toggleable(isToggledHiddenByDefault: true),

TextColumn::make('updated_at')
    ->label(__('supplier.updated_at'))
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
            'index' => ListSuppliers::route('/'),
            'create' => CreateSupplier::route('/create'),
            'edit' => EditSupplier::route('/{record}/edit'),
        ];
    }
}