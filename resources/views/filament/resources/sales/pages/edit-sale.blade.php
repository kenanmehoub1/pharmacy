<x-filament-panels::page>
    <style>
        .sal-container { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
        .sal-card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .sal-title { font-size: 18px; font-weight: bold; margin-bottom: 15px; }
        .sal-input { width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; }
        .sal-results { margin-top: 15px; max-height: 500px; overflow-y: auto; display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; }
        .sal-result { padding: 12px; border: 1px solid #e5e7eb; border-radius: 8px; cursor: pointer; transition: all 0.2s; background: white; }
        .sal-result:hover { border-color: #16a34a; background: #f0fdf4; }
        .sal-result-name { font-weight: bold; font-size: 14px; }
        .sal-result-info { font-size: 12px; color: #6b7280; margin-top: 5px; display: flex; justify-content: space-between; }
        .sal-cart-item { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f3f4f6; }
        .sal-cart-name { font-weight: bold; font-size: 13px; }
        .sal-cart-qty { font-size: 11px; color: #6b7280; }
        .sal-remove { color: #ef4444; cursor: pointer; background: none; border: none; font-weight: bold; font-size: 16px; }
        .sal-summary { border-top: 1px solid #e5e7eb; padding-top: 15px; margin-top: 15px; }
        .sal-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px; align-items: center; }
        .sal-total { display: flex; justify-content: space-between; font-weight: bold; font-size: 18px; border-top: 1px solid #e5e7eb; padding-top: 12px; margin-top: 12px; color: #16a34a; }
        .sal-input-discount { width: 80px; padding: 4px 8px; border: 1px solid #d1d5db; border-radius: 6px; text-align: end; }
        .sal-save { width: 100%; background: #16a34a; color: white; padding: 12px; border-radius: 10px; border: none; font-weight: bold; font-size: 16px; cursor: pointer; margin-top: 15px; }
        .sal-save:hover { background: #15803d; }
        .sal-save:disabled { opacity: 0.5; cursor: not-allowed; }
        .sal-empty { text-align: center; color: #9ca3af; padding: 20px; }
        .sal-warning { background: #fef3c7; border: 1px solid #f59e0b; border-radius: 8px; padding: 10px; margin-top: 10px; font-size: 12px; color: #92400e; }
        .sal-error { color: #ef4444; font-size: 12px; margin-top: 5px; }
        
        @media (max-width: 1024px) {
            .sal-container { grid-template-columns: 1fr; }
            .sal-results { grid-template-columns: 1fr; }
        }
    </style>

    <div class="sal-container">
        {{-- البحث --}}
        <div class="sal-card">
            <h2 class="sal-title">🔍 {{ __('sale.search_medicine') }}</h2>
            
            <input 
                type="text" 
                wire:model.live.debounce.300ms="search"
                placeholder="{{ __('sale.search_placeholder') }}"
                class="sal-input"
            />

            <div class="sal-results">
                @forelse ($medicines as $medicine)
                    <div class="sal-result" wire:click="addToCart({{ $medicine['id'] }})">
                        <div class="sal-result-name">{{ $medicine['name'] }}</div>
                        <div class="sal-result-info">
                            <span>{{ $medicine['scientific_name'] }}</span>
                            <span>
                                <strong style="color: #16a34a;">{{ number_format($medicine['price'], 2) }} {{ $currency }}</strong>
                                | {{ __('medicine.stock') }}: {{ $medicine['stock'] }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="sal-empty" style="grid-column: span 2;">{{ __('sale.no_results') }}</p>
                @endforelse
            </div>
        </div>

        {{-- الفاتورة --}}
        <div class="sal-card">
            <h2 class="sal-title">🧾 {{ __('sale.invoice') }}</h2>

          {{-- العميل --}}
<div style="margin-bottom: 15px;">
    <label class="pur-label">{{ __('sale.customer') }}</label>
    
    {{-- Radio Buttons --}}
    <div style="display: flex; gap: 15px; margin-bottom: 10px;">
        <label style="display: flex; align-items: center; gap: 5px; cursor: pointer; font-size: 13px;">
            <input type="radio" wire:model.live="customerType" value="existing" />
            {{ __('sale.existing_customer') }}
        </label>
        <label style="display: flex; align-items: center; gap: 5px; cursor: pointer; font-size: 13px;">
            <input type="radio" wire:model.live="customerType" value="new" />
            {{ __('sale.new_customer') }}
        </label>
    </div>

    @if ($customerType === 'existing')
        <select wire:model="customerId" class="sal-input">
            <option value="">{{ __('sale.walk_in') }}</option>
            @foreach ($customers as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
        </select>
    @endif

    @if ($customerType === 'new')
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
            <div>
                <label class="pur-label">{{ __('customer.name') }} *</label>
                <input type="text" wire:model="newCustomer.name" class="sal-input" />
                @error('newCustomer.name') <span class="sal-error">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="pur-label">{{ __('customer.phone') }}</label>
                <input type="text" wire:model="newCustomer.phone" class="sal-input" />
            </div>
        </div>
    @endif

    @if ($paymentMethod === 'credit' && !$customerId && $customerType === 'existing')
        <p class="sal-error">⚠️ {{ __('sale.credit_requires_customer') }}</p>
    @endif
</div>

            {{-- طريقة الدفع --}}
            <div class="sal-row">
                <label>{{ __('sale.payment_method') }}</label>
                <select wire:model.live="paymentMethod" class="sal-input" style="width: 60%;">
                    <option value="cash">{{ __('sale.cash') }}</option>
                    <option value="card">{{ __('sale.card') }}</option>
                    <option value="credit">{{ __('sale.credit') }}</option>
                </select>
            </div>

            @if ($paymentMethod === 'credit')
                <div class="sal-warning">
                    ⚠️ {{ __('sale.credit_note') }}
                    @if ($customerId)
                        <br><strong>{{ __('sale.amount_added_to_balance') }}: {{ number_format($total, 2) }} {{ $currency }}</strong>
                    @endif
                </div>
            @endif

            <hr style="margin: 15px 0;">

            @if (empty($cart))
                <p class="sal-empty">{{ __('sale.cart_empty') }}</p>
            @else
                <div style="max-height: 250px; overflow-y: auto;">
                    @foreach ($cart as $id => $item)
                        <div class="sal-cart-item">
                            <div>
                                <div class="sal-cart-name">{{ $item['name'] }}</div>
                                <div class="sal-cart-qty">{{ $item['quantity'] }} × {{ number_format($item['price'], 2) }} {{ $currency }}</div>
                            </div>
                            <button wire:click="removeFromCart({{ $id }})" class="sal-remove">✕</button>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="sal-summary">
                <div class="sal-row">
                    <span>{{ __('sale.subtotal') }}</span>
                    <span>{{ number_format($subtotal, 2) }} {{ $currency }}</span>
                </div>
                <div class="sal-row">
                    <span>{{ __('sale.discount') }}</span>
                    <input type="number" wire:model.live="discount" class="sal-input-discount" min="0" />
                </div>
                @if ($taxRate > 0)
                    <div class="sal-row">
                        <span>{{ __('sale.tax') }} ({{ $taxRate }}%)</span>
                        <span>{{ number_format($taxAmount, 2) }} {{ $currency }}</span>
                    </div>
                @endif
                <div class="sal-total">
                    <span>{{ __('sale.total') }}</span>
                    <span>{{ number_format($total, 2) }} {{ $currency }}</span>
                </div>
            </div>

            <button wire:click="saveSale" @if(empty($cart)) disabled @endif class="sal-save">
                ✓ {{ __('sale.update') }}
            </button>
        </div>
    </div>
</x-filament-panels::page>