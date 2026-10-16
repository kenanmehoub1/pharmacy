<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Sales\SaleResource;
use App\Models\Sale;
use App\Models\Setting;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class DashboardStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $currency = Setting::get('currency', 'EGP');

        // مبيعات اليوم
        $todaySales = Sale::whereDate('created_at', Carbon::today())->sum('total') ?? 0;
        $todayInvoices = Sale::whereDate('created_at', Carbon::today())->count();

        // أرباح اليوم
        $todayProfit = Sale::whereDate('created_at', Carbon::today())->sum('profit') ?? 0;

        // أمس للمقارنة
        $yesterdaySales = Sale::whereDate('created_at', Carbon::yesterday())->sum('total') ?? 0;
        $yesterdayProfit = Sale::whereDate('created_at', Carbon::yesterday())->sum('profit') ?? 0;

        $profitChange = $yesterdayProfit > 0
            ? round((($todayProfit - $yesterdayProfit) / $yesterdayProfit) * 100, 1)
            : 0;

        return [
            Stat::make(__('dashboard.today_sales'), number_format($todaySales, 2) . ' ' . $currency)
                ->description($todayInvoices . ' ' . __('dashboard.invoices'))
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('success')
                ->url(SaleResource::getUrl('index', [
                    'tableFilters' => [
                        'created_at' => [
                            'today' => true,
                        ],
                    ],
                ])),

            Stat::make(__('dashboard.today_profit'), number_format($todayProfit, 2) . ' ' . $currency)
                ->description($profitChange >= 0
                    ? '+' . $profitChange . '% ' . __('dashboard.vs_yesterday')
                    : $profitChange . '% ' . __('dashboard.vs_yesterday'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color($profitChange >= 0 ? 'success' : 'danger')
                ->url(SaleResource::getUrl('index', [
                    'tableFilters' => [
                        'created_at' => [
                            'today' => true,
                        ],
                    ],
                ])),
        ];
    }
}