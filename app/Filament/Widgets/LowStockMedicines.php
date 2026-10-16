<?php

namespace App\Filament\Widgets;

use App\Models\Medicine;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class LowStockMedicines extends TableWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 1;

    protected static ?string $heading = null;

    public function getTableHeading(): string
    {
        return __('dashboard.low_stock_medicines');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                fn (): Builder => Medicine::query()
                    ->whereColumn('stock', '<=', 'min_stock')
                    ->where('stock', '>', 0)
                    ->orderBy('stock')
            )
            ->columns([
                TextColumn::make('name')
                    ->label(__('medicine.name'))
                    ->searchable()
                    ->limit(20),

                TextColumn::make('company_name')
                    ->label(__('medicine.company_name'))
                    ->searchable()
                    ->limit(15),

                TextColumn::make('stock')
                    ->label(__('medicine.stock'))
                    ->badge()
                    ->color('danger'),

                TextColumn::make('min_stock')
                    ->label(__('medicine.min_stock'))
                    ->badge()
                    ->color('gray'),
            ])
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10);
    }
}