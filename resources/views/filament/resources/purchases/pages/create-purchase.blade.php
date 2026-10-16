<x-filament-panels::page>
    <style>
        .pur-container { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
        .pur-card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .pur-title { font-size: 18px; font-weight: bold; margin-bottom: 15px; }
        .pur-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
        .pur-field { display: flex; flex-direction: column; }
        .pur-label { font-size: 12px; color: #374151; font-weight: 600; margin-bottom: 5px; }
        .pur-input { padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; width: 100%; }
        .pur-input:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
        .pur-full { grid-column: span 2; }
        .pur-add-btn { background: #3b82f6; color: white; padding: 10px 20px; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; margin-top: 15px; width: 100%; font-size: 15px; }
        .pur-add-btn:hover { background: #2563eb; }
        .pur-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px; align-items: center; }
        .pur-cart-item { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f3f4f6; }
        .pur-cart-info { flex: 1; }
        .pur-cart-name { font-weight: bold; font-size: 13px; }
        .pur-cart-qty { font-size: 11px; color: #6b7280; }
        .pur-remove { color: #ef4444; cursor: pointer; background: none; border: none; font-weight: bold; font-size: 16px; }
        .pur-total { display: flex; justify-content: space-between; font-weight: bold; font-size: 18px; border-top: 2px solid #3b82f6; padding-top: 12px; margin-top: 12px; color: #3b82f6; }
        .pur-save { width: 100%; background: #16a34a; color: white; padding: 14px; border-radius: 10px; border: none; font-weight: bold; font-size: 16px; cursor: pointer; margin-top: 15px; }
        .pur-save:hover { background: #15803d; }
        .pur-save:disabled { opacity: 0.5; cursor: not-allowed; }
        .pur-error { color: #ef4444; font-size: 11px; margin-top: 3px; }
        
        @media (max-width: 1024px) {
            .pur-container { grid-template-columns: 1fr; }
            .pur-grid { grid-template-columns: 1fr; }
            .pur-full { grid-column: span 1; }
        }
    </style>

    <div class="pur-container">
        {{-- نموذج إدخال الدواء --}}
        <div class="pur-card">
            <h2 class="pur-title">💊 {{ __('purchase.add_medicine') }}</h2>
            
            <div class="pur-grid">
                <div class="pur-field">
                    <label class="pur-label">{{ __('medicine.name') }} *</label>
                    <input type="text" wire:model="newMedicine.name" class="pur-input" />
                    @error('newMedicine.name') <span class="pur-error">{{ $message }}</span> @enderror
                </div>

                <div class="pur-field">
                    <label class="pur-label">{{ __('medicine.scientific_name') }}</label>
                    <input type="text" wire:model="newMedicine.scientific_name" class="pur-input" />
                </div>

                <div class="pur-field">
                    <label class="pur-label">{{ __('medicine.company_name') }}</label>
                    <input type="text" wire:model="newMedicine.company_name" class="pur-input" />
                </div>

                <div class="pur-field">
                    <label class="pur-label">{{ __('medicine.barcode') }}</label>
                    <input type="text" wire:model="newMedicine.barcode" class="pur-input" />
                </div>

                <div class="pur-field">
                    <label class="pur-label">{{ __('medicine.category') }}</label>
                    <input type="text" wire:model="newMedicine.category" class="pur-input" />
                </div>

                <div class="pur-field">
                    <label class="pur-label">{{ __('purchase.quantity') }} *</label>
                    <input type="number" wire:model="newMedicine.quantity" min="1" class="pur-input" />
                    @error('newMedicine.quantity') <span class="pur-error">{{ $message }}</span> @enderror
                </div>

                <div class="pur-field">
                    <label class="pur-label">{{ __('medicine.cost') }} *</label>
                    <input type="number" wire:model="newMedicine.cost" step="0.01" class="pur-input" />
                    @error('newMedicine.cost') <span class="pur-error">{{ $message }}</span> @enderror
                </div>

                <div class="pur-field">
                    <label class="pur-label">{{ __('medicine.price') }} *</label>
                    <input type="number" wire:model="newMedicine.price" step="0.01" class="pur-input" />
                    @error('newMedicine.price') <span class="pur-error">{{ $message }}</span> @enderror
                </div>

                <div class="pur-field">
                    <label class="pur-label">{{ __('medicine.expiry_date') }}</label>
                    <input type="date" wire:model="newMedicine.expiry_date" class="pur-input" />
                </div>

                <div class="pur-field">
                    <label class="pur-label">{{ __('medicine.min_stock') }}</label>
                    <input type="number" wire:model="newMedicine.min_stock" min="0" class="pur-input" />
                </div>

                <div class="pur-field pur-full">
                    <label class="pur-label">{{ __('medicine.description') }}</label>
                    <textarea wire:model="newMedicine.description" rows="2" class="pur-input"></textarea>
                </div>
            </div>

            <button wire:click="addToCart" class="pur-add-btn">
                ➕ {{ __('purchase.add_to_invoice') }}
            </button>
        </div>

        {{-- الفاتورة --}}
        <div class="pur-card">
            <h2 class="pur-title">🧾 {{ __('purchase.invoice') }}</h2>

      {{-- المورد --}}
<div style="margin-bottom: 15px;">
    <label class="pur-label">{{ __('purchase.supplier') }} *</label>
    
    {{-- Radio Buttons --}}
    <div style="display: flex; gap: 15px; margin-bottom: 10px;">
        <label style="display: flex; align-items: center; gap: 5px; cursor: pointer; font-size: 13px;">
            <input type="radio" wire:model.live="supplierType" value="existing" />
            {{ __('purchase.existing_supplier') }}
        </label>
        <label style="display: flex; align-items: center; gap: 5px; cursor: pointer; font-size: 13px;">
            <input type="radio" wire:model.live="supplierType" value="new" />
            {{ __('purchase.new_supplier') }}
        </label>
    </div>

    {{-- مورد موجود --}}
    @if ($supplierType === 'existing')
        <select wire:model="supplierId" class="pur-input">
            <option value="">{{ __('purchase.select_supplier') }}</option>
            @foreach ($suppliers as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
        </select>
    @endif

    {{-- مورد جديد --}}
    @if ($supplierType === 'new')
        <div class="pur-grid">
            <div class="pur-field">
                <label class="pur-label">{{ __('supplier.name') }} *</label>
                <input type="text" wire:model="newSupplier.name" class="pur-input" />
                @error('newSupplier.name') <span class="pur-error">{{ $message }}</span> @enderror
            </div>
            <div class="pur-field">
                <label class="pur-label">{{ __('supplier.phone') }}</label>
                <input type="text" wire:model="newSupplier.phone" class="pur-input" />
            </div>
            <div class="pur-field pur-full">
                <label class="pur-label">{{ __('supplier.address') }}</label>
                <input type="text" wire:model="newSupplier.address" class="pur-input" />
            </div>
        </div>
    @endif
</div>

            <div class="pur-row">
                <label>{{ __('purchase.invoice_number') }}</label>
                <span style="font-weight: bold; font-size: 13px;">{{ $invoiceNumber }}</span>
            </div>

            <div class="pur-row">
                <label>{{ __('purchase.payment_method') }}</label>
                <select wire:model="paymentMethod" class="pur-input" style="width: 60%;">
                    <option value="cash">{{ __('purchase.cash') }}</option>
                    <option value="card">{{ __('purchase.card') }}</option>
                    <option value="credit">{{ __('purchase.credit') }}</option>
                </select>
            </div>

            <hr style="margin: 15px 0;">

            @if (empty($cart))
                <p style="text-align: center; color: #9ca3af; padding: 20px;">{{ __('purchase.cart_empty') }}</p>
            @else
                <div style="max-height: 300px; overflow-y: auto;">
                    @foreach ($cart as $id => $item)
                        <div class="pur-cart-item">
                            <div class="pur-cart-info">
                                <div class="pur-cart-name">{{ $item['name'] }}</div>
                                <div class="pur-cart-qty">{{ $item['quantity'] }} × {{ number_format($item['cost'], 2) }} {{ $currency }}</div>
                            </div>
                            <button wire:click="removeFromCart({{ $id }})" class="pur-remove">✕</button>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="pur-total">
                <span>{{ __('purchase.total') }}</span>
                <span>{{ number_format($total, 2) }} {{ $currency }}</span>
            </div>

            <button wire:click="save" @if(empty($cart)) disabled @endif class="pur-save">
                ✓ {{ __('purchase.save') }}
            </button>
        </div>
    </div>
</x-filament-panels::page>