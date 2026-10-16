<?php

namespace App\Filament\Widgets;

use App\Models\Medicine;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class ExpiredMedicines extends TableWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 1;

    protected static ?string $heading = null;

    public function getTableHeading(): string
    {
        return __('dashboard.expiring_soon_medicines');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                fn (): Builder => Medicine::query()
                    ->whereNotNull('expiry_date')
                    ->whereDate('expiry_date', '>=', Carbon::today())
                    ->whereDate('expiry_date', '<=', Carbon::today()->addDays(90))
                    ->orderBy('expiry_date')
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

                TextColumn::make('expiry_date')
                    ->label(__('medicine.expiry_date'))
                    ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->format('d/m/Y') : '-')
                    ->badge()
                    ->color('warning'),
            ])
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10);
    }
}