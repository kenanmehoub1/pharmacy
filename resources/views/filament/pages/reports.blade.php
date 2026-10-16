<x-filament-panels::page>
    <style>
        .rep-container { padding: 10px; }
        .rep-stats { display: grid; grid-template-columns: repeat(5, 1fr); gap: 15px; margin-bottom: 20px; }
        .rep-stat { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .rep-stat-label { font-size: 13px; color: #6b7280; margin-bottom: 8px; }
        .rep-stat-value { font-size: 24px; font-weight: bold; color: #111827; }
        .rep-stat.success .rep-stat-value { color: #16a34a; }
        .rep-stat.info .rep-stat-value { color: #3b82f6; }
        .rep-stat.warning .rep-stat-value { color: #f59e0b; }
        .rep-stat.danger .rep-stat-value { color: #ef4444; }
        .rep-card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 20px; }
        .rep-card-title { font-size: 16px; font-weight: bold; margin-bottom: 15px; }
        .rep-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .rep-table { width: 100%; border-collapse: collapse; }
        .rep-table th { text-align: start; padding: 10px; border-bottom: 2px solid #e5e7eb; font-size: 13px; color: #6b7280; }
        .rep-table td { padding: 10px; border-bottom: 1px solid #f3f4f6; font-size: 14px; }
        .rep-badge { display: inline-block; padding: 3px 8px; border-radius: 6px; font-size: 12px; font-weight: bold; background: #dbeafe; color: #1e40af; }
        .rep-bar-container { display: flex; align-items: end; gap: 8px; height: 150px; padding: 10px 0; }
        .rep-bar-wrapper { flex: 1; display: flex; flex-direction: column; align-items: center; height: 100%; justify-content: end; }
        .rep-bar { width: 100%; background: linear-gradient(to top, #16a34a, #4ade80); border-radius: 6px 6px 0 0; min-height: 4px; transition: all 0.3s; }
        .rep-bar:hover { opacity: 0.8; }
        .rep-bar-label { font-size: 11px; color: #6b7280; margin-top: 6px; }
        .rep-bar-value { font-size: 11px; font-weight: bold; color: #16a34a; margin-bottom: 4px; }
        .rep-empty { text-align: center; color: #9ca3af; padding: 20px; }
        @media (max-width: 1024px) {
    .rep-stats { 
        grid-template-columns: repeat(2, 1fr) !important; 
    }
    .rep-grid-2 { 
        grid-template-columns: 1fr !important; 
    }
}

@media (max-width: 640px) {
    .rep-stats { 
        grid-template-columns: 1fr !important; 
    }
    .rep-table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }
    .rep-bar-container {
        overflow-x: auto;
    }
}
    </style>

    <div class="rep-container">
        {{-- الإحصائيات --}}
        <div class="rep-stats">
            <div class="rep-stat success">
                <div class="rep-stat-label">💵 {{ __('report.total_sales') }}</div>
                <div class="rep-stat-value">{{ number_format($totalSales, 0) }}</div>
            </div>
            <div class="rep-stat info">
                <div class="rep-stat-label">🛒 {{ __('report.total_purchases') }}</div>
                <div class="rep-stat-value">{{ number_format($totalPurchases, 0) }}</div>
            </div>
            <div class="rep-stat warning">
                <div class="rep-stat-label">📈 {{ __('report.total_profit') }}</div>
                <div class="rep-stat-value">{{ number_format($totalProfit, 0) }}</div>
            </div>
            <div class="rep-stat">
                <div class="rep-stat-label">🧾 {{ __('report.total_invoices') }}</div>
                <div class="rep-stat-value">{{ $totalInvoices }}</div>
            </div>
            <div class="rep-stat">
                <div class="rep-stat-label">💊 {{ __('report.total_medicines') }}</div>
                <div class="rep-stat-value">{{ $totalMedicines }}</div>
            </div>
        </div>

        {{-- المبيعات اليومية --}}
        <div class="rep-card">
            <div class="rep-card-title">📊 {{ __('report.daily_sales_7days') }}</div>
            @php
                $maxSale = collect($dailySales)->max('total') ?: 1;
            @endphp
            <div class="rep-bar-container">
                @foreach ($dailySales as $day)
                    <div class="rep-bar-wrapper">
                        <div class="rep-bar-value">{{ number_format($day['total'], 0) }}</div>
                        <div class="rep-bar" style="height: {{ ($day['total'] / $maxSale) * 100 }}%;"></div>
                        <div class="rep-bar-label">{{ $day['date'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rep-grid-2">
            {{-- أفضل الأدوية --}}
            <div class="rep-card">
                <div class="rep-card-title">🏆 {{ __('report.top_medicines') }}</div>
                @if (empty($topMedicines))
                    <p class="rep-empty">{{ __('report.no_data') }}</p>
                @else
                    <table class="rep-table">
                        <thead>
                            <tr>
                                <th>{{ __('medicine.name') }}</th>
                                <th>{{ __('report.qty_sold') }}</th>
                                <th>{{ __('report.sales') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($topMedicines as $item)
                                <tr>
                                    <td>{{ $item['medicine']['name'] ?? 'N/A' }}</td>
                                    <td><span class="rep-badge">{{ $item['total_qty'] }}</span></td>
                                    <td>{{ number_format($item['total_sales'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            {{-- أفضل العملاء --}}
            <div class="rep-card">
                <div class="rep-card-title">👑 {{ __('report.top_customers') }}</div>
                @if (empty($topCustomers))
                    <p class="rep-empty">{{ __('report.no_data') }}</p>
                @else
                    <table class="rep-table">
                        <thead>
                            <tr>
                                <th>{{ __('customer.name') }}</th>
                                <th>{{ __('report.invoices') }}</th>
                                <th>{{ __('report.total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($topCustomers as $customer)
                                <tr>
                                    <td>{{ $customer['name'] }}</td>
                                    <td><span class="rep-badge">{{ $customer['sales_count'] }}</span></td>
                                    <td>{{ number_format($customer['sales_sum_total'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        {{-- الأرباح الشهرية --}}
        <div class="rep-card">
            <div class="rep-card-title">💰 {{ __('report.monthly_profit_6months') }}</div>
            <table class="rep-table">
                <thead>
                    <tr>
                        <th>{{ __('report.month') }}</th>
                        <th>{{ __('report.sales') }}</th>
                        <th>{{ __('report.profit') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($monthlyProfit as $month)
                        <tr>
                            <td>{{ $month['month'] }}</td>
                            <td>{{ number_format($month['sales'], 2) }} EGP</td>
                            <td style="color: #16a34a; font-weight: bold;">{{ number_format($month['profit'], 2) }} EGP</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>