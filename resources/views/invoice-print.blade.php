<!DOCTYPE html>
<html dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('sale.invoice') }} - {{ $sale->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', sans-serif; padding: 20px; background: white; color: #111; }
        .invoice { max-width: 800px; margin: 0 auto; border: 1px solid #e5e7eb; padding: 30px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #16a34a; padding-bottom: 20px; }
        .pharmacy-name { font-size: 28px; font-weight: bold; color: #16a34a; margin-bottom: 10px; }
        .pharmacy-info { font-size: 13px; color: #6b7280; line-height: 1.8; }
        .invoice-info { display: flex; justify-content: space-between; margin-bottom: 25px; font-size: 14px; }
        .invoice-info div { line-height: 1.8; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th { background: #f3f4f6; padding: 12px; text-align: start; font-size: 13px; color: #374151; border-bottom: 2px solid #d1d5db; }
        td { padding: 12px; border-bottom: 1px solid #e5e7eb; font-size: 14px; }
        .totals { margin-top: 20px; display: flex; justify-content: flex-end; }
        .totals-table { width: 300px; }
        .totals-table tr td { border: none; padding: 6px 0; }
        .totals-table tr td:last-child { text-align: end; font-weight: bold; }
        .total-row { font-size: 18px; font-weight: bold; border-top: 2px solid #16a34a; padding-top: 12px !important; color: #16a34a; }
        .footer { text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px dashed #d1d5db; color: #6b7280; font-size: 13px; }
        .no-print { text-align: center; margin-top: 20px; }
        .no-print button { background: #16a34a; color: white; padding: 10px 30px; border: none; border-radius: 8px; font-size: 16px; cursor: pointer; margin: 5px; font-family: inherit; }
        .no-print button:hover { background: #15803d; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
            .invoice { border: none; padding: 0; }
        }
        @media (max-width: 640px) {
    body { padding: 10px; }
    .invoice { padding: 15px; }
    .invoice-info { flex-direction: column; gap: 15px; }
    table { font-size: 12px; }
    th, td { padding: 8px 4px; }
    .totals-table { width: 100%; }
}
    </style>
</head>
<body>
    @php
        $currency = \App\Models\Setting::get('currency', 'EGP');
        $pharmacyName = \App\Models\Setting::get('pharmacy_name', 'صيدليتي');
        $pharmacyAddress = \App\Models\Setting::get('pharmacy_address', '');
        $pharmacyPhone = \App\Models\Setting::get('pharmacy_phone', '');
        $pharmacyEmail = \App\Models\Setting::get('pharmacy_email', '');
        $invoiceFooter = \App\Models\Setting::get('invoice_footer', __('sale.thank_you'));
    @endphp

    <div class="invoice">
        <div class="header">
            <div class="pharmacy-name">{{ $pharmacyName }}</div>
            <div class="pharmacy-info">
                @if($pharmacyAddress) 📍 {{ $pharmacyAddress }}<br> @endif
                @if($pharmacyPhone) 📞 {{ $pharmacyPhone }} @endif
                @if($pharmacyEmail) &nbsp;|&nbsp; ✉️ {{ $pharmacyEmail }} @endif
            </div>
        </div>

        <div class="invoice-info">
            <div>
                <strong>{{ __('sale.invoice_number') }}:</strong> {{ $sale->invoice_number }}<br>
                <strong>{{ __('sale.date') }}:</strong> {{ $sale->created_at->format('d/m/Y H:i') }}
            </div>
            <div>
                <strong>{{ __('sale.customer') }}:</strong>
                {{ $sale->customer?->name ?? __('sale.walk_in') }}<br>
                @if($sale->customer?->phone)
                    <strong>{{ __('customer.phone') }}:</strong> {{ $sale->customer->phone }}
                @endif
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('medicine.name') }}</th>
                    <th>{{ __('sale.quantity') }}</th>
                    <th>{{ __('sale.price') }}</th>
                    <th>{{ __('sale.subtotal') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sale->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->medicine->name ?? 'N/A' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->price, 2) }} {{ $currency }}</td>
                        <td>{{ number_format($item->subtotal, 2) }} {{ $currency }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <table class="totals-table">
                <tr>
                    <td>{{ __('sale.subtotal') }}:</td>
                    <td>{{ number_format($sale->subtotal, 2) }} {{ $currency }}</td>
                </tr>
                @if($sale->discount > 0)
                    <tr>
                        <td>{{ __('sale.discount') }}:</td>
                        <td>- {{ number_format($sale->discount, 2) }} {{ $currency }}</td>
                    </tr>
                @endif
                <tr class="total-row">
                    <td>{{ __('sale.total') }}:</td>
                    <td>{{ number_format($sale->total, 2) }} {{ $currency }}</td>
                </tr>
            </table>
        </div>

        <div class="footer">{{ $invoiceFooter }}</div>
    </div>

    {{-- زر الطباعة --}}
    <div class="no-print">
        <button onclick="window.print()">🖨️ {{ __('sale.print') }}</button>
        <button onclick="window.close()">✖ {{ __('sale.close') }}</button>
    </div>
</body>
</html>