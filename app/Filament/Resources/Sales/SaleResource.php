<?php

namespace App\Filament\Resources\Sales;

use App\Filament\Resources\Sales\Pages\CreateSale;
use App\Filament\Resources\Sales\Pages\EditSale;
use App\Filament\Resources\Sales\Pages\ListSales;
use App\Models\Sale;
use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Table;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;
    public static function canCreate(): bool
{
    return false;
}

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?int $navigationSort = 5;

    public static function getNavigationLabel(): string
    {
        return __('navigation.sales');
    }

    public static function getModelLabel(): string
    {
        return __('sale.title');
    }

    public static function getPluralModelLabel(): string
    {
        return __('sale.title_plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('invoice_number')
                    ->label(__('sale.invoice_number'))
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->default(fn () => Setting::get('invoice_prefix', 'INV-') . strtoupper(uniqid()))
                    ->maxLength(255),

                Select::make('customer_id')
                    ->label(__('sale.customer'))
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),

                TextInput::make('subtotal')
                    ->label(__('sale.subtotal'))
                    ->numeric()
                    ->default(0)
                    ->prefix(fn () => Setting::get('currency', 'EGP')),

                TextInput::make('discount')
                    ->label(__('sale.discount'))
                    ->numeric()
                    ->default(0)
                    ->prefix(fn () => Setting::get('currency', 'EGP')),

                TextInput::make('total')
                    ->label(__('sale.total'))
                    ->numeric()
                    ->default(0)
                    ->prefix(fn () => Setting::get('currency', 'EGP')),

                TextInput::make('profit')
                    ->label(__('sale.profit'))
                    ->numeric()
                    ->default(0)
                    ->prefix(fn () => Setting::get('currency', 'EGP')),

                Select::make('payment_method')
                    ->label(__('sale.payment_method'))
                    ->options([
                        'cash' => __('sale.cash'),
                        'card' => __('sale.card'),
                        'credit' => __('sale.credit'),
                    ])
                    ->default('cash')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')
                    ->label(__('sale.invoice_number'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('customer.name')
                    ->label(__('sale.customer'))
                    ->default(__('sale.walk_in'))
                    ->searchable(),

                TextColumn::make('total')
                    ->label(__('sale.total'))
                    ->money(fn () => Setting::get('currency', 'EGP'))
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('profit')
                    ->label(__('sale.profit'))
                    ->money(fn () => Setting::get('currency', 'EGP'))
                    ->color('success')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('payment_method')
                    ->label(__('sale.payment_method'))
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'cash' => 'success',
                        'card' => 'info',
                        'credit' => 'warning',
                    })
                    ->formatStateUsing(fn ($state) => __('sale.' . $state)),

               TextColumn::make('created_at')
    ->label(__('sale.date'))
    ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->format('d/m/Y') : '-')
    ->sortable(),

TextColumn::make('updated_at')
    ->label(__('sale.updated_at'))
    ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->format('d/m/Y') : '-')
    ->sortable()
    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')->label(__('sale.from')),
                        DatePicker::make('created_until')->label(__('sale.until')),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['created_from'], fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['created_until'], fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators[] = Indicator::make(__('sale.from') . ': ' . $data['created_from']);
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators[] = Indicator::make(__('sale.until') . ': ' . $data['created_until']);
                        }
                        return $indicators;
                    }),

                Filter::make('today')
                    ->label(__('sale.today'))
                    ->query(fn ($query) => $query->whereDate('created_at', today()))
                    ->toggle(),
            ])
            ->recordActions([
                Action::make('print')
                    ->label(__('sale.print'))
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->url(fn ($record) => route('invoice.print', ['sale' => $record->id]))
                    ->openUrlInNewTab(),
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
            'index' => ListSales::route('/'),
            
            'edit' => EditSale::route('/{record}/edit'),
        ];
    }
}