<?php

namespace App\Filament\Resources\Purchases;

use App\Filament\Resources\Purchases\Pages\CreatePurchase;
use App\Filament\Resources\Purchases\Pages\EditPurchase;
use App\Filament\Resources\Purchases\Pages\ListPurchases;
use App\Models\Purchase;
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

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?int $navigationSort = 6;

    public static function getNavigationLabel(): string
    {
        return __('navigation.purchases');
    }

    public static function getModelLabel(): string
    {
        return __('purchase.title');
    }

    public static function getPluralModelLabel(): string
    {
        return __('purchase.title_plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('invoice_number')
                    ->label(__('purchase.invoice_number'))
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->default(fn () => Setting::get('invoice_prefix', 'PUR-') . strtoupper(uniqid()))
                    ->maxLength(255),

                Select::make('supplier_id')
                    ->label(__('purchase.supplier'))
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('total')
                    ->label(__('purchase.total'))
                    ->numeric()
                    ->default(0)
                    ->prefix(fn () => Setting::get('currency', 'EGP')),

                Select::make('payment_method')
                    ->label(__('purchase.payment_method'))
                    ->options([
                        'cash' => __('purchase.cash'),
                        'card' => __('purchase.card'),
                        'credit' => __('purchase.credit'),
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
                    ->label(__('purchase.invoice_number'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('supplier.name')
                    ->label(__('purchase.supplier'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('items_count')
                    ->label(__('purchase.items_count'))
                    ->counts('items')
                    ->badge()
                    ->color('info'),

                TextColumn::make('total')
                    ->label(__('purchase.total'))
                    ->money(fn () => Setting::get('currency', 'EGP'))
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('payment_method')
                    ->label(__('purchase.payment_method'))
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'cash' => 'success',
                        'card' => 'info',
                        'credit' => 'warning',
                    })
                    ->formatStateUsing(fn ($state) => __('purchase.' . $state)),

               TextColumn::make('created_at')
    ->label(__('purchase.date'))
    ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->format('d/m/Y') : '-')
    ->sortable(),

TextColumn::make('updated_at')
    ->label(__('purchase.updated_at'))
    ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->format('d/m/Y') : '-')
    ->sortable()
    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')->label(__('purchase.from')),
                        DatePicker::make('created_until')->label(__('purchase.until')),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['created_from'], fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['created_until'], fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators[] = Indicator::make(__('purchase.from') . ': ' . $data['created_from']);
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators[] = Indicator::make(__('purchase.until') . ': ' . $data['created_until']);
                        }
                        return $indicators;
                    }),

                Filter::make('today')
                    ->label(__('purchase.today'))
                    ->query(fn ($query) => $query->whereDate('created_at', today()))
                    ->toggle(),
            ])
            ->recordActions([
                Action::make('print')
                    ->label(__('purchase.print'))
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->url(fn ($record) => route('purchase.print', ['purchase' => $record->id]))
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
            'index' => ListPurchases::route('/'),
            'create' => CreatePurchase::route('/create'),
            'edit' => EditPurchase::route('/{record}/edit'),
        ];
    }
}