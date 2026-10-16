<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Medicines\MedicineResource;
use App\Models\Medicine;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class AlertStats extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        // تنتهي خلال 90 يوم
        $expiringIn90 = Medicine::whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>=', Carbon::today())
            ->whereDate('expiry_date', '<=', Carbon::today()->addDays(90))
            ->count();

        // مخزون منخفض
        $lowStock = Medicine::whereColumn('stock', '<=', 'min_stock')
            ->where('stock', '>', 0)
            ->count();

        return [
            Stat::make(__('dashboard.expiring_in_90'), $expiringIn90)
                ->description(__('dashboard.days_90'))
                ->descriptionIcon('heroicon-m-calendar')
                ->color('warning')
                ->url(MedicineResource::getUrl('index', [
                    'tableFilters' => [
                        'expiring_soon' => [
                            'isActive' => true,
                        ],
                    ],
                ])),

            Stat::make(__('dashboard.low_stock'), $lowStock)
                ->description(__('dashboard.low_stock_desc'))
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('danger')
                ->url(MedicineResource::getUrl('index', [
                    'tableFilters' => [
                        'low_stock' => [
                            'isActive' => true,
                        ],
                    ],
                ])),
        ];
    }
}