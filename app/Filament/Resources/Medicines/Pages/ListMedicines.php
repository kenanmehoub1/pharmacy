<?php

namespace App\Filament\Resources\Medicines\Pages;

use App\Filament\Resources\Medicines\MedicineResource;
use App\Filament\Resources\Purchases\PurchaseResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListMedicines extends ListRecords
{
    protected static string $resource = MedicineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('add_via_purchase')
                ->label(__('medicine.add_via_purchase'))
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->url(fn () => PurchaseResource::getUrl('create')),
        ];
    }
}