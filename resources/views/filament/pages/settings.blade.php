<x-filament-panels::page>
    <style>
        .set-container { padding: 10px; max-width: 900px; }
        .set-card { background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 20px; }
        .set-card-title { font-size: 18px; font-weight: bold; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #f3f4f6; }
        .set-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .set-field { display: flex; flex-direction: column; }
        .set-label { font-size: 13px; color: #374151; font-weight: 600; margin-bottom: 6px; }
        .set-input { padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; width: 100%; }
        .set-input:focus { outline: none; border-color: #16a34a; box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.1); }
        .set-full { grid-column: span 2; }
        .set-save-btn { background: #16a34a; color: white; padding: 12px 30px; border: none; border-radius: 10px; font-size: 16px; font-weight: bold; cursor: pointer; transition: background 0.2s; }
        .set-save-btn:hover { background: #15803d; }
    </style>

    <div class="set-container">
        {{-- معلومات الصيدلية --}}
        <div class="set-card">
            <div class="set-card-title">🏪 {{ __('settings.pharmacy_info') }}</div>
            <div class="set-grid">
                <div class="set-field">
                    <label class="set-label">{{ __('settings.pharmacy_name') }}</label>
                    <input type="text" wire:model="pharmacyName" class="set-input" />
                </div>
                <div class="set-field">
                    <label class="set-label">{{ __('settings.pharmacy_phone') }}</label>
                    <input type="text" wire:model="pharmacyPhone" class="set-input" />
                </div>
                <div class="set-field">
                    <label class="set-label">{{ __('settings.pharmacy_email') }}</label>
                    <input type="email" wire:model="pharmacyEmail" class="set-input" />
                </div>
                <div class="set-field">
                    <label class="set-label">{{ __('settings.pharmacy_address') }}</label>
                    <input type="text" wire:model="pharmacyAddress" class="set-input" />
                </div>
            </div>
        </div>

        {{-- العملة والضريبة --}}
        <div class="set-card">
            <div class="set-card-title">💰 {{ __('settings.currency_tax') }}</div>
            <div class="set-grid">
                <div class="set-field">
                    <label class="set-label">{{ __('settings.currency') }}</label>
                    <select wire:model="currency" class="set-input">
                        <option value="EGP">جنيه مصري (EGP)</option>
                        <option value="SAR">ريال سعودي (SAR)</option>
                        <option value="AED">درهم إماراتي (AED)</option>
                        <option value="USD">دولار أمريكي (USD)</option>
                        <option value="SYP">ليرة سورية (SYP)</option>
                    </select>
                </div>
                <div class="set-field">
                    <label class="set-label">{{ __('settings.tax_rate') }} (%)</label>
                    <input type="number" wire:model="taxRate" class="set-input" min="0" max="100" step="0.1" />
                </div>
            </div>
        </div>

        {{-- إعدادات الفاتورة --}}
        <div class="set-card">
            <div class="set-card-title">🧾 {{ __('settings.invoice_settings') }}</div>
            <div class="set-grid">
                <div class="set-field">
                    <label class="set-label">{{ __('settings.invoice_prefix') }}</label>
                    <input type="text" wire:model="invoicePrefix" class="set-input" />
                </div>
                <div class="set-field set-full">
                    <label class="set-label">{{ __('settings.invoice_footer') }}</label>
                    <textarea wire:model="invoiceFooter" class="set-input" rows="3" placeholder="{{ __('settings.invoice_footer_placeholder') }}"></textarea>
                </div>
            </div>
        </div>

        <button wire:click="save" class="set-save-btn">
            ✓ {{ __('settings.save') }}
        </button>
    </div>
</x-filament-panels::page>