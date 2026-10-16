<?php

namespace App\Filament\Pages;

use App\Models\Customer;
use App\Models\Medicine;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class Reports extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?int $navigationSort = 10;

    protected static ?string $slug = 'reports';

    protected string $view = 'filament.pages.reports';

    public static function getNavigationLabel(): string
    {
        return __('navigation.reports');
    }

    // ملخص عام
    public float $totalSales = 0;
    public float $totalPurchases = 0;
    public float $totalProfit = 0;
    public int $totalInvoices = 0;
    public int $totalMedicines = 0;

    // أفضل الأدوية
    public array $topMedicines = [];

    // أفضل العملاء
    public array $topCustomers = [];

    // المبيعات اليومية (آخر 7 أيام)
    public array $dailySales = [];

    // الأرباح الشهرية
    public array $monthlyProfit = [];

    public function mount(): void
    {
        $this->loadSummary();
        $this->loadTopMedicines();
        $this->loadTopCustomers();
        $this->loadDailySales();
        $this->loadMonthlyProfit();
    }

    public function loadSummary(): void
    {
        $this->totalSales = (float) Sale::sum('total');
        $this->totalPurchases = (float) Purchase::sum('total');
        $this->totalProfit = (float) Sale::sum('profit');
        $this->totalInvoices = Sale::count();
        $this->totalMedicines = Medicine::count();
    }

    public function loadTopMedicines(): void
    {
        $this->topMedicines = SaleItem::query()
            ->select('medicine_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_sales'))
            ->with('medicine:id,name')
            ->groupBy('medicine_id')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get()
            ->toArray();
    }

    public function loadTopCustomers(): void
    {
        $this->topCustomers = Customer::query()
            ->withSum('sales', 'total')
            ->withCount('sales')
            ->having('sales_sum_total', '>', 0)
            ->orderByDesc('sales_sum_total')
            ->limit(10)
            ->get()
            ->toArray();
    }

    public function loadDailySales(): void
    {
        $this->dailySales = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            
            $this->dailySales[] = [
                'date' => $date->format('d/m'),
                'day' => $date->translatedFormat('D'),
                'total' => (float) Sale::whereDate('created_at', $date)->sum('total'),
                'count' => Sale::whereDate('created_at', $date)->count(),
            ];
        }
    }

    public function loadMonthlyProfit(): void
    {
        $this->monthlyProfit = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::today()->subMonths($i);
            
            $this->monthlyProfit[] = [
                'month' => $date->translatedFormat('M Y'),
                'profit' => (float) Sale::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->sum('profit'),
                'sales' => (float) Sale::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->sum('total'),
            ];
        }
    }
}