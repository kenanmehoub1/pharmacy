<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use App\Models\Setting;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class SalesChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '280px';  // ← أضف هذا

    public function getHeading(): string
    {
        return __('dashboard.sales_last_7_days');
    }

    protected function getData(): array
    {
        $currency = Setting::get('currency', 'EGP');

        $labels = [];
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->translatedFormat('D d/m');
            $data[] = (float) Sale::whereDate('created_at', $date)->sum('total');
        }

        return [
            'datasets' => [
                [
                    'label' => __('dashboard.sales') . ' (' . $currency . ')',
                    'data' => $data,
                    'backgroundColor' => '#16a34a',
                    'borderColor' => '#16a34a',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}