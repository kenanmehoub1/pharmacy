<?php

namespace App\Filament\Resources\Sales\Pages;

use App\Filament\Pages\Pos;
use App\Filament\Resources\Sales\SaleResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListSales extends ListRecords
{
    protected static string $resource = SaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('new_sale')
                ->label(__('sale.new_sale'))
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->url(fn () => Pos::getUrl()),
        ];
    }
}