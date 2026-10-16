<x-filament-panels::page>
    <style>
        .pos-container { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
        .pos-medicines { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .pos-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-top: 15px; }
        .pos-item { border: 2px solid #e5e7eb; border-radius: 10px; padding: 12px; cursor: pointer; transition: all 0.2s; text-align: start; background: white; width: 100%; }
        .pos-item:hover { border-color: #16a34a; background: #f0fdf4; }
        .pos-item-name { font-weight: bold; font-size: 14px; color: #111827; }
        .pos-item-sci { font-size: 11px; color: #6b7280; margin-top: 2px; }
        .pos-item-footer { display: flex; justify-content: space-between; align-items: center; margin-top: 10px; }
        .pos-price { color: #16a34a; font-weight: bold; font-size: 14px; }
        .pos-stock { font-size: 11px; color: #9ca3af; }
        .pos-cart { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .pos-cart-title { font-size: 18px; font-weight: bold; margin-bottom: 15px; }
        .pos-empty { text-align: center; color: #9ca3af; padding: 30px 0; }
        .pos-cart-item { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f3f4f6; }
        .pos-cart-item-info { flex: 1; }
        .pos-cart-item-name { font-weight: bold; font-size: 13px; }
        .pos-cart-item-qty { font-size: 11px; color: #6b7280; }
        .pos-remove { color: #ef4444; cursor: pointer; background: none; border: none; font-weight: bold; font-size: 16px; }
        .pos-summary { border-top: 1px solid #e5e7eb; padding-top: 15px; margin-top: 15px; }
        .pos-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px; align-items: center; }
        .pos-total { display: flex; justify-content: space-between; font-weight: bold; font-size: 18px; border-top: 1px solid #e5e7eb; padding-top: 12px; margin-top: 12px; }
        .pos-total-value { color: #16a34a; }
        .pos-input { width: 80px; border: 1px solid #d1d5db; border-radius: 6px; padding: 4px 8px; text-align: end; }
        .pos-checkout { width: 100%; background: #16a34a; color: white; padding: 12px; border-radius: 10px; border: none; font-weight: bold; font-size: 16px; cursor: pointer; margin-top: 15px; }
        .pos-checkout:hover { background: #15803d; }
        .pos-checkout:disabled { opacity: 0.5; cursor: not-allowed; }
        .pos-print-btn { width: 100%; background: white; color: #16a34a; padding: 10px; border-radius: 10px; border: 2px solid #16a34a; font-weight: bold; font-size: 14px; cursor: pointer; margin-top: 10px; }
        .pos-print-btn:hover { background: #16a34a; color: white; }
        
        /* زر السلة العائم - مخفي على الكمبيوتر */
        .pos-cart-fab {
            display: none;
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #16a34a;
            color: white;
            border: none;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            font-size: 24px;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(22, 163, 74, 0.4);
            z-index: 999;
            align-items: center;
            justify-content: center;
        }
        .pos-cart-fab .pos-cart-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ef4444;
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }
        
        /* نافذة السلة المنبثقة - مخفية افتراضياً */
        .pos-cart-modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: flex-end;
        }
        .pos-cart-modal-content {
            background: white;
            width: 100%;
            max-height: 85vh;
            border-radius: 20px 20px 0 0;
            padding: 20px;
            overflow-y: auto;
            position: relative;
        }
        .pos-cart-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f3f4f6;
        }
        .pos-cart-modal-title {
            font-size: 20px;
            font-weight: bold;
        }
        .pos-cart-modal-close {
            background: none;
            border: none;
            font-size: 28px;
            cursor: pointer;
            color: #6b7280;
            line-height: 1;
        }
        
        /* ===== Responsive ===== */
        
        /* Tablet & Mobile */
        @media (max-width: 1024px) {
            .pos-container { 
                grid-template-columns: 1fr !important; 
            }
            .pos-grid { 
                grid-template-columns: repeat(2, 1fr) !important; 
            }
            /* إخفاء السلة الثابتة */
            .pos-cart-desktop {
                display: none !important;
            }
            /* إظهار الزر العائم */
            .pos-cart-fab {
                display: flex;
            }
        }
        
        @media (max-width: 640px) {
            .pos-grid { 
                grid-template-columns: 1fr !important; 
            }
            .pos-medicines {
                padding: 15px;
            }
        }
    </style>

    <div class="pos-container">
        {{-- الأدوية --}}
        <div class="pos-medicines">
            <h2 style="font-size: 20px; font-weight: bold;">💊 {{ __('pos.medicines') }}</h2>
            <div style="margin-bottom: 15px;">
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search"
                    x-on:keydown.enter.prevent="
                        let val = $event.target.value;
                        if (val.length > 3) {
                            $wire.searchByBarcode(val);
                            $event.target.value = '';
                        }
                    "
                    placeholder="🔍 {{ __('pos.search_placeholder') }}"
                    style="width: 100%; padding: 10px 15px; border: 1px solid #d1d5db; border-radius: 10px; font-size: 14px;"
                />
            </div>
            <div class="pos-grid">
                @forelse ($medicines as $medicine)
                    <button wire:click="addToCart({{ $medicine['id'] }})" class="pos-item">
                        <div class="pos-item-name">{{ $medicine['name'] }}</div>
                        <div class="pos-item-sci">{{ $medicine['scientific_name'] }}</div>
                        <div class="pos-item-footer">
                            <span class="pos-price">{{ number_format($medicine['price'], 2) }}</span>
                            <span class="pos-stock">{{ __('pos.stock') }}: {{ $medicine['stock'] }}</span>
                        </div>
                    </button>
                @empty
                    <p style="grid-column: span 3; text-align: center; color: #9ca3af; padding: 30px;">
                        {{ __('pos.no_results') }}
                    </p>
                @endforelse
            </div>
        </div>

        {{-- السلة - سطح المكتب --}}
        <div class="pos-cart pos-cart-desktop">
            @include('filament.pages.partials.pos-cart-content')
        </div>
    </div>

    {{-- زر السلة العائم (للموبايل) --}}
    <button class="pos-cart-fab" onclick="document.getElementById('posCartModal').style.display='flex'">
        🛒
        @if(count($cart) > 0)
            <span class="pos-cart-count">{{ count($cart) }}</span>
        @endif
    </button>

    {{-- نافذة السلة المنبثقة --}}
    <div id="posCartModal" class="pos-cart-modal" onclick="if(event.target === this) this.style.display='none'">
        <div class="pos-cart-modal-content">
            <div class="pos-cart-modal-header">
                <span class="pos-cart-modal-title">🛒 {{ __('pos.cart') }}</span>
                <button class="pos-cart-modal-close" onclick="document.getElementById('posCartModal').style.display='none'">✕</button>
            </div>
            @include('filament.pages.partials.pos-cart-content')
        </div>
    </div>

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('open-invoice', (event) => {
                window.open(event.url, '_blank', 'noopener,noreferrer');
            });
            
            // إغلاق النافذة عند إتمام البيع
            Livewire.on('close-cart-modal', () => {
                document.getElementById('posCartModal').style.display = 'none';
            });
        });
    </script>
</x-filament-panels::page>