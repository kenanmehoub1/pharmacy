<?php

namespace App\Filament\Pages;

use App\Models\Medicine;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;

class Inventory extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-archive-box';

    protected static ?int $navigationSort = 4;

    protected static ?string $slug = 'inventory';

    protected string $view = 'filament.pages.inventory';

    public static function getNavigationLabel(): string
    {
        return __('navigation.inventory');
    }

    // إحصائيات
    public int $totalMedicines = 0;
    public int $lowStockCount = 0;
    public int $outOfStockCount = 0;
    public int $expiredCount = 0;
    public int $expiringSoonCount = 0;
    public float $totalStockValue = 0;

    // قوائم
    public array $lowStockMedicines = [];
    public array $expiredMedicines = [];
    public array $expiringSoonMedicines = [];

    public function mount(): void
    {
        $this->loadStats();
        $this->loadLists();
    }

    public function loadStats(): void
    {
        $this->totalMedicines = Medicine::count();
        
        $this->lowStockCount = Medicine::whereColumn('stock', '<=', 'min_stock')
            ->where('stock', '>', 0)
            ->count();
        
        $this->outOfStockCount = Medicine::where('stock', '<=', 0)->count();
        
        $this->expiredCount = Medicine::whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<', Carbon::today())
            ->count();
        
        $this->expiringSoonCount = Medicine::whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>=', Carbon::today())
            ->whereDate('expiry_date', '<=', Carbon::today()->addDays(90))
            ->count();

        $this->totalStockValue = Medicine::query()
            ->selectRaw('SUM(stock * cost) as total')
            ->value('total') ?? 0;
    }

    public function loadLists(): void
    {
        $this->lowStockMedicines = Medicine::whereColumn('stock', '<=', 'min_stock')
            ->where('stock', '>', 0)
            ->orderBy('stock')
            ->limit(20)
            ->get()
            ->toArray();

        $this->expiredMedicines = Medicine::whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<', Carbon::today())
            ->orderBy('expiry_date')
            ->limit(20)
            ->get()
            ->toArray();

        $this->expiringSoonMedicines = Medicine::whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>=', Carbon::today())
            ->whereDate('expiry_date', '<=', Carbon::today()->addDays(90))
            ->orderBy('expiry_date')
            ->limit(20)
            ->get()
            ->toArray();
    }
}