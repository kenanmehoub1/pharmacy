<x-filament-panels::page>
    <style>
        .inv-container { padding: 10px; }
        .inv-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 20px; }
        .inv-stat { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border-left: 4px solid #16a34a; }
        .inv-stat.danger { border-left-color: #ef4444; }
        .inv-stat.warning { border-left-color: #f59e0b; }
        .inv-stat.info { border-left-color: #3b82f6; }
        .inv-stat-label { font-size: 13px; color: #6b7280; margin-bottom: 8px; }
        .inv-stat-value { font-size: 28px; font-weight: bold; color: #111827; }
        .inv-card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 15px; }
        .inv-card-title { font-size: 16px; font-weight: bold; margin-bottom: 15px; display: flex; align-items: center; gap: 8px; }
        .inv-table { width: 100%; border-collapse: collapse; }
        .inv-table th { text-align: start; padding: 10px; border-bottom: 2px solid #e5e7eb; font-size: 13px; color: #6b7280; }
        .inv-table td { padding: 10px; border-bottom: 1px solid #f3f4f6; font-size: 14px; }
        .inv-table tr:last-child td { border-bottom: none; }
        .inv-empty { text-align: center; color: #9ca3af; padding: 20px; }
        .inv-badge { display: inline-block; padding: 3px 8px; border-radius: 6px; font-size: 12px; font-weight: bold; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-success { background: #dcfce7; color: #166534; }
        @media (max-width: 1024px) {
    .inv-stats { 
        grid-template-columns: repeat(2, 1fr) !important; 
    }
    .inv-grid-2 { 
        grid-template-columns: 1fr !important; 
    }
}

@media (max-width: 640px) {
    .inv-stats { 
        grid-template-columns: 1fr !important; 
    }
    .inv-table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }
}
    </style>

    <div class="inv-container">
        {{-- الإحصائيات --}}
        <div class="inv-stats">
            <div class="inv-stat">
                <div class="inv-stat-label">📦 {{ __('inventory.total_medicines') }}</div>
                <div class="inv-stat-value">{{ $totalMedicines }}</div>
            </div>
            <div class="inv-stat warning">
                <div class="inv-stat-label">⚠️ {{ __('inventory.low_stock') }}</div>
                <div class="inv-stat-value">{{ $lowStockCount }}</div>
            </div>
            <div class="inv-stat danger">
                <div class="inv-stat-label">❌ {{ __('inventory.out_of_stock') }}</div>
                <div class="inv-stat-value">{{ $outOfStockCount }}</div>
            </div>
            <div class="inv-stat danger">
                <div class="inv-stat-label">🚫 {{ __('inventory.expired') }}</div>
                <div class="inv-stat-value">{{ $expiredCount }}</div>
            </div>
            <div class="inv-stat warning">
                <div class="inv-stat-label">⏰ {{ __('inventory.expiring_soon') }}</div>
                <div class="inv-stat-value">{{ $expiringSoonCount }}</div>
            </div>
            <div class="inv-stat info">
                <div class="inv-stat-label">💰 {{ __('inventory.total_value') }}</div>
                <div class="inv-stat-value">{{ number_format($totalStockValue, 2) }}</div>
            </div>
        </div>

        {{-- المخزون المنخفض --}}
        <div class="inv-card">
            <div class="inv-card-title">⚠️ {{ __('inventory.low_stock_list') }}</div>
            @if (empty($lowStockMedicines))
                <p class="inv-empty">{{ __('inventory.no_low_stock') }}</p>
            @else
                <table class="inv-table">
                    <thead>
                        <tr>
                            <th>{{ __('medicine.name') }}</th>
                            <th>{{ __('medicine.stock') }}</th>
                            <th>{{ __('medicine.min_stock') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lowStockMedicines as $medicine)
                            <tr>
                                <td>{{ $medicine['name'] }}</td>
                                <td><span class="inv-badge badge-warning">{{ $medicine['stock'] }}</span></td>
                                <td>{{ $medicine['min_stock'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        {{-- منتهية الصلاحية --}}
        <div class="inv-card">
            <div class="inv-card-title">🚫 {{ __('inventory.expired_list') }}</div>
            @if (empty($expiredMedicines))
                <p class="inv-empty">{{ __('inventory.no_expired') }}</p>
            @else
                <table class="inv-table">
                    <thead>
                        <tr>
                            <th>{{ __('medicine.name') }}</th>
                            <th>{{ __('medicine.expiry_date') }}</th>
                            <th>{{ __('medicine.stock') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($expiredMedicines as $medicine)
                            <tr>
                                <td>{{ $medicine['name'] }}</td>
                                <td><span class="inv-badge badge-danger">{{ \Carbon\Carbon::parse($medicine['expiry_date'])->format('Y-m-d') }}</span></td>
                                <td>{{ $medicine['stock'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        {{-- تنتهي قريباً --}}
        <div class="inv-card">
            <div class="inv-card-title">⏰ {{ __('inventory.expiring_soon_list') }}</div>
            @if (empty($expiringSoonMedicines))
                <p class="inv-empty">{{ __('inventory.no_expiring_soon') }}</p>
            @else
                <table class="inv-table">
                    <thead>
                        <tr>
                            <th>{{ __('medicine.id') }}</th>
                            <th>{{ __('medicine.name') }}</th>
                            <th>{{ __('medicine.expiry_date') }}</th>
                            <th>{{ __('medicine.stock') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($expiringSoonMedicines as $medicine)
                            <tr>
                                 <td>{{ $medicine['id'] }}</td>
                                <td>{{ $medicine['name'] }}</td>
                                <td><span class="inv-badge badge-warning">{{ \Carbon\Carbon::parse($medicine['expiry_date'])->format('Y-m-d') }}</span></td>
                                <td>{{ $medicine['stock'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</x-filament-panels::page>