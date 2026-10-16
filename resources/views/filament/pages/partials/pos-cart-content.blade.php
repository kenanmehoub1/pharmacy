{{-- العميل --}}
<div style="margin-bottom: 15px;">
    <label style="display: block; font-size: 13px; color: #6b7280; margin-bottom: 5px;">
        👤 {{ __('pos.customer') }}
    </label>
    
    <div style="display: flex; gap: 15px; margin-bottom: 10px;">
        <label style="display: flex; align-items: center; gap: 5px; cursor: pointer; font-size: 13px;">
            <input type="radio" wire:model.live="customerType" value="existing" />
            {{ __('pos.existing_customer') }}
        </label>
        <label style="display: flex; align-items: center; gap: 5px; cursor: pointer; font-size: 13px;">
            <input type="radio" wire:model.live="customerType" value="new" />
            {{ __('pos.new_customer') }}
        </label>
    </div>

    @if ($customerType === 'existing')
        <select wire:model="customerId" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 8px; background: white;">
            <option value="">{{ __('pos.walk_in') }}</option>
            @foreach ($customers as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
        </select>
    @endif

    @if ($customerType === 'new')
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
            <input type="text" wire:model="newCustomer.name" placeholder="{{ __('customer.name') }} *" style="padding: 8px; border: 1px solid #d1d5db; border-radius: 8px;" />
            <input type="text" wire:model="newCustomer.phone" placeholder="{{ __('customer.phone') }}" style="padding: 8px; border: 1px solid #d1d5db; border-radius: 8px;" />
        </div>
    @endif
</div>

{{-- طريقة الدفع --}}
<div style="margin-bottom: 15px;">
    <label style="display: block; font-size: 13px; color: #6b7280; margin-bottom: 5px;">
        💳 {{ __('pos.payment_method') }}
    </label>
    <select wire:model.live="paymentMethod" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 8px; background: white;">
        <option value="cash">{{ __('pos.cash') }}</option>
        <option value="card">{{ __('pos.card') }}</option>
        <option value="credit">{{ __('pos.credit') }}</option>
    </select>
    
    @if ($paymentMethod === 'credit')
        <div style="background: #fef3c7; border: 1px solid #f59e0b; border-radius: 8px; padding: 8px; margin-top: 8px; font-size: 12px; color: #92400e;">
            ⚠️ {{ __('pos.credit_note') }}
        </div>
    @endif
</div>

<h2 class="pos-cart-title">🛒 {{ __('pos.cart') }}</h2>

@if (empty($cart))
    <p class="pos-empty">{{ __('pos.cart_empty') }}</p>
@else
    <div style="max-height: 250px; overflow-y: auto;">
        @foreach ($cart as $id => $item)
            <div class="pos-cart-item">
                <div class="pos-cart-item-info">
                    <div class="pos-cart-item-name">{{ $item['name'] }}</div>
                    <div class="pos-cart-item-qty">{{ $item['quantity'] }} × {{ number_format($item['price'], 2) }}</div>
                </div>
                <button wire:click="removeFromCart({{ $id }})" class="pos-remove">✕</button>
            </div>
        @endforeach
    </div>
@endif

<div class="pos-summary">
    <div class="pos-row">
        <span>{{ __('pos.subtotal') }}</span>
        <span>{{ number_format($subtotal, 2) }} {{ $currency }}</span>
    </div>
    <div class="pos-row">
        <span>{{ __('pos.discount') }}</span>
        <input type="number" wire:model.live="discount" class="pos-input" min="0" />
    </div>
    @if ($taxRate > 0)
        <div class="pos-row">
            <span>{{ __('pos.tax') }} ({{ $taxRate }}%)</span>
            <span>{{ number_format($taxAmount, 2) }} {{ $currency }}</span>
        </div>
    @endif
    <div class="pos-total">
        <span>{{ __('pos.total') }}</span>
        <span class="pos-total-value">{{ number_format($total, 2) }} {{ $currency }}</span>
    </div>
</div>

<button wire:click="checkout" @if(empty($cart)) disabled @endif class="pos-checkout">
    ✓ {{ __('pos.checkout') }}
</button>

@if ($lastSaleId)
    <button wire:click="printLastSale" class="pos-print-btn">
        🖨️ {{ __('pos.print_last') }}
    </button>
@endif