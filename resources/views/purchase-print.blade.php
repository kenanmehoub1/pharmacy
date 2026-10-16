<!DOCTYPE html>
<html dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('purchase.title') }} - {{ $purchase->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', sans-serif; padding: 20px; background: white; color: #111; }
        .invoice { max-width: 800px; margin: 0 auto; border: 1px solid #e5e7eb; padding: 30px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #3b82f6; padding-bottom: 20px; }
        .pharmacy-name { font-size: 28px; font-weight: bold; color: #3b82f6; margin-bottom: 10px; }
        .pharmacy-info { font-size: 13px; color: #6b7280; line-height: 1.8; }
        .invoice-info { display: flex; justify-content: space-between; margin-bottom: 25px; font-size: 14px; }
        .invoice-info div { line-height: 1.8; }
        .invoice-info strong { color: #374151; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th { background: #eff6ff; padding: 12px; text-align: start; font-size: 13px; color: #1e40af; border-bottom: 2px solid #3b82f6; }
        td { padding: 12px; border-bottom: 1px solid #e5e7eb; font-size: 14px; }
        .totals { margin-top: 20px; display: flex; justify-content: flex-end; }
        .totals-table { width: 300px; }
        .totals-table tr td { border: none; padding: 6px 0; }
        .totals-table tr td:last-child { text-align: end; font-weight: bold; }
        .total-row { font-size: 18px; font-weight: bold; border-top: 2px solid #3b82f6; padding-top: 12px !important; color: #3b82f6; }
        .footer { text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px dashed #d1d5db; color: #6b7280; font-size: 13px; }
        .no-print { text-align: center; margin-top: 20px; }
        .no-print button { background: #3b82f6; color: white; padding: 10px 30px; border: none; border-radius: 8px; font-size: 16px; cursor: pointer; margin: 5px; font-family: inherit; }
        .no-print button:hover { background: #2563eb; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
            .invoice { border: none; padding: 0; }
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
        {{-- رأس الفاتورة --}}
        <div class="header">
            <div class="pharmacy-name">{{ $pharmacyName }}</div>
            <div class="pharmacy-info">
                @if($pharmacyAddress) 📍 {{ $pharmacyAddress }}<br> @endif
                @if($pharmacyPhone) 📞 {{ $pharmacyPhone }} @endif
                @if($pharmacyEmail) &nbsp;|&nbsp; ✉️ {{ $pharmacyEmail }} @endif
            </div>
        </div>

        {{-- عنوان الفاتورة --}}
        <div style="text-align: center; margin-bottom: 20px;">
            <h2 style="font-size: 22px; color: #1e40af;">{{ __('purchase.invoice') }}</h2>
        </div>

        {{-- معلومات الفاتورة --}}
        <div class="invoice-info">
            <div>
                <strong>{{ __('purchase.invoice_number') }}:</strong> {{ $purchase->invoice_number }}<br>
                <strong>{{ __('purchase.date') }}:</strong> {{ $purchase->created_at->format('d/m/Y H:i') }}
            </div>
            <div>
                <strong>{{ __('purchase.supplier') }}:</strong> {{ $purchase->supplier?->name ?? 'N/A' }}<br>
                @if($purchase->supplier?->phone)
                    <strong>{{ __('supplier.phone') }}:</strong> {{ $purchase->supplier->phone }}<br>
                @endif
                @if($purchase->supplier?->address)
                    <strong>{{ __('supplier.address') }}:</strong> {{ $purchase->supplier->address }}
                @endif
            </div>
        </div>

        {{-- جدول الأدوية --}}
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('medicine.name') }}</th>
                    <th>{{ __('medicine.company_name') }}</th>
                    <th>{{ __('purchase.quantity') }}</th>
                    <th>{{ __('medicine.cost') }}</th>
                    <th>{{ __('purchase.subtotal') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($purchase->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $item->medicine->name ?? 'N/A' }}</strong>
                            @if($item->medicine?->scientific_name)
                                <br><small style="color: #6b7280;">{{ $item->medicine->scientific_name }}</small>
                            @endif
                        </td>
                        <td>{{ $item->medicine->company_name ?? '-' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->cost, 2) }} {{ $currency }}</td>
                        <td><strong>{{ number_format($item->subtotal, 2) }} {{ $currency }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- الإجماليات --}}
        <div class="totals">
            <table class="totals-table">
                <tr>
                    <td>{{ __('purchase.total_items') }}:</td>
                    <td>{{ $purchase->items->count() }}</td>
                </tr>
                <tr>
                    <td>{{ __('purchase.total_quantity') }}:</td>
                    <td>{{ $purchase->items->sum('quantity') }}</td>
                </tr>
                <tr>
                    <td>{{ __('purchase.payment_method') }}:</td>
                    <td>{{ __('purchase.' . $purchase->payment_method) }}</td>
                </tr>
                <tr class="total-row">
                    <td>{{ __('purchase.total') }}:</td>
                    <td>{{ number_format($purchase->total, 2) }} {{ $currency }}</td>
                </tr>
            </table>
        </div>

        {{-- تذييل --}}
        <div class="footer">
            {{ $invoiceFooter }}
        </div>
    </div>

    {{-- أزرار التحكم --}}
    <div class="no-print">
        <button onclick="window.print()">🖨️ {{ __('sale.print') }}</button>
        <button onclick="window.close()">✖ {{ __('sale.close') }}</button>
    </div>
</body>
</html>