<?php

namespace App\Filament\Resources\Medicines;

use App\Filament\Resources\Medicines\Pages\EditMedicine;
use App\Filament\Resources\Medicines\Pages\ListMedicines;
use App\Models\Medicine;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;


class MedicineResource extends Resource
{
    protected static ?string $model = Medicine::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cube';

    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return __('navigation.products');
    }

    public static function canCreate(): bool
    {
        return false;
    }
   

    public static function getModelLabel(): string
    {
        return __('medicine.title');
    }

    public static function getPluralModelLabel(): string
    {
        return __('medicine.title_plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('medicine.name'))
                    ->required()
                    ->maxLength(255),

                TextInput::make('scientific_name')
                    ->label(__('medicine.scientific_name'))
                    ->maxLength(255),

                TextInput::make('company_name')
                    ->label(__('medicine.company_name'))
                    ->maxLength(255),

                TextInput::make('barcode')
                    ->label(__('medicine.barcode'))
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                TextInput::make('category')
                    ->label(__('medicine.category'))
                    ->maxLength(255),

                TextInput::make('price')
                    ->label(__('medicine.price'))
                    ->required()
                    ->numeric()
                    ->prefix(fn () => \App\Models\Setting::get('currency', 'EGP')),

                TextInput::make('cost')
                    ->label(__('medicine.cost'))
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false)
                    ->prefix(fn () => \App\Models\Setting::get('currency', 'EGP'))
                    ->helperText(__('medicine.cost_managed_by_invoices')),

                TextInput::make('stock')
                    ->label(__('medicine.stock'))
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText(__('medicine.stock_managed_by_invoices')),

                TextInput::make('min_stock')
                    ->label(__('medicine.min_stock'))
                    ->required()
                    ->numeric()
                    ->default(10),

                DatePicker::make('expiry_date')
                    ->label(__('medicine.expiry_date')),

                Textarea::make('description')
                    ->label(__('medicine.description'))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('medicine.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('company_name')
                    ->label(__('medicine.company_name'))
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('barcode')
                    ->label(__('medicine.barcode'))
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('category')
                    ->label(__('medicine.category'))
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('price')
                    ->label(__('medicine.price'))
                    ->money(fn () => \App\Models\Setting::get('currency', 'EGP'))
                    ->sortable(),

                TextColumn::make('stock')
                    ->label(__('medicine.stock'))
                    ->badge()
                    ->color(fn ($record) => $record->isLowStock() ? 'danger' : 'success')
                    ->sortable(),

                TextColumn::make('expiry_date')
                    ->label(__('medicine.expiry_date'))
                    ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->format('d/m/Y') : '-')
                    ->badge()
                    ->color(function ($record) {
                        if (!$record->expiry_date) {
                            return 'gray';
                        }

                        $daysLeft = now()->diffInDays($record->expiry_date, false);

                        if ($daysLeft <= 90) {
                            return 'danger';
                        }

                        return 'success';
                    })
                    ->sortable(),

               TextColumn::make('created_at')
    ->label(__('medicine.created_at'))
    ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->format('d/m/Y') : '-')
    ->sortable()
    ->toggleable(isToggledHiddenByDefault: true),

TextColumn::make('updated_at')
    ->label(__('medicine.updated_at'))
    ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->format('d/m/Y') : '-')
    ->sortable()
    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('low_stock')
                    ->label(__('medicine.low_stock_filter'))
                    ->query(fn ($query) => $query
                        ->whereColumn('stock', '<=', 'min_stock')
                        ->where('stock', '>', 0))
                    ->toggle(),

                Filter::make('expiring_soon')
                    ->label(__('medicine.expiring_soon_filter'))
                    ->query(fn ($query) => $query
                        ->whereNotNull('expiry_date')
                        ->whereDate('expiry_date', '>=', now())
                        ->whereDate('expiry_date', '<=', now()->addDays(90)))
                    ->toggle(),

                Filter::make('expired')
                    ->label(__('medicine.expired_filter'))
                    ->query(fn ($query) => $query
                        ->whereNotNull('expiry_date')
                        ->whereDate('expiry_date', '<', now()))
                    ->toggle(),
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
            'index' => ListMedicines::route('/'),
           
            'edit' => EditMedicine::route('/{record}/edit'),
        ];
    }
}