<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Settings extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?int $navigationSort = 11;

    protected static ?string $slug = 'settings';

    protected string $view = 'filament.pages.settings';

    public static function getNavigationLabel(): string
    {
        return __('navigation.settings');
    }

    // معلومات الصيدلية
    public string $pharmacyName = '';
    public string $pharmacyAddress = '';
    public string $pharmacyPhone = '';
    public string $pharmacyEmail = '';

    // العملة والضريبة
    public string $currency = 'EGP';
    public float $taxRate = 0;

    // إعدادات الفاتورة
    public string $invoicePrefix = 'INV-';
    public string $invoiceFooter = '';

    public function mount(): void
    {
        $this->pharmacyName = Setting::get('pharmacy_name', 'صيدليتي');
        $this->pharmacyAddress = Setting::get('pharmacy_address', '');
        $this->pharmacyPhone = Setting::get('pharmacy_phone', '');
        $this->pharmacyEmail = Setting::get('pharmacy_email', '');
        $this->currency = Setting::get('currency', 'EGP');
        $this->taxRate = (float) Setting::get('tax_rate', 0);
        $this->invoicePrefix = Setting::get('invoice_prefix', 'INV-');
        $this->invoiceFooter = Setting::get('invoice_footer', '');
    }

    public function save(): void
    {
        Setting::set('pharmacy_name', $this->pharmacyName);
        Setting::set('pharmacy_address', $this->pharmacyAddress);
        Setting::set('pharmacy_phone', $this->pharmacyPhone);
        Setting::set('pharmacy_email', $this->pharmacyEmail);
        Setting::set('currency', $this->currency);
        Setting::set('tax_rate', $this->taxRate);
        Setting::set('invoice_prefix', $this->invoicePrefix);
        Setting::set('invoice_footer', $this->invoiceFooter);

        Notification::make()
            ->title(__('settings.saved_successfully'))
            ->success()
            ->send();
    }
}