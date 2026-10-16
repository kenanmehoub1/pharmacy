<?php

namespace App\Filament\Pages;

use App\Models\Customer;
use App\Models\Medicine;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Setting;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Pos extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-computer-desktop';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'pos';

    protected string $view = 'filament.pages.pos';

    public static function getNavigationLabel(): string
    {
        return __('navigation.pos');
    }

    // الأدوية
    public array $medicines = [];

    // العميل
    public ?int $customerId = null;
    public array $customers = [];
    public string $customerType = 'existing';
    public array $newCustomer = [
        'name' => '',
        'phone' => '',
    ];

    // السلة
    public array $cart = [];
    public float $subtotal = 0;
    public float $discount = 0;
    public float $taxRate = 0;
    public float $taxAmount = 0;
    public float $total = 0;
    public ?int $lastSaleId = null;

    // طرق الدفع
    public string $paymentMethod = 'cash';

    // البحث
    public string $search = '';

    // العملة
    public string $currency = 'EGP';

    public function mount(): void
    {
        $this->loadMedicines();
        $this->loadCustomers();
        $this->taxRate = (float) Setting::get('tax_rate', 0);
        $this->currency = Setting::get('currency', 'EGP');
        $this->recalculate();
    }

    public function loadCustomers(): void
    {
        $this->customers = Customer::query()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function loadMedicines(): void
    {
        $query = Medicine::query()->where('stock', '>', 0);

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('scientific_name', 'like', '%' . $this->search . '%')
                  ->orWhere('barcode', 'like', '%' . $this->search . '%');
            });
        }

        $this->medicines = $query->orderBy('name')->limit(50)->get()->toArray();
    }

    public function updatedSearch(): void
    {
        $this->loadMedicines();
    }

    public function addToCart(int $medicineId): void
    {
        $medicine = Medicine::find($medicineId);
        if (!$medicine || $medicine->stock <= 0) {
            return;
        }

        if (isset($this->cart[$medicineId])) {
            if ($this->cart[$medicineId]['quantity'] < $medicine->stock) {
                $this->cart[$medicineId]['quantity']++;
                $this->cart[$medicineId]['subtotal'] =
                    $this->cart[$medicineId]['quantity'] * $this->cart[$medicineId]['price'];
            }
        } else {
            $this->cart[$medicineId] = [
                'id' => $medicine->id,
                'name' => $medicine->name,
                'price' => (float) $medicine->price,
                'cost' => (float) $medicine->cost,
                'quantity' => 1,
                'subtotal' => (float) $medicine->price,
            ];
        }

        $this->recalculate();
    }

    public function removeFromCart(int $medicineId): void
    {
        unset($this->cart[$medicineId]);
        $this->recalculate();
    }

    public function updatedDiscount(): void
    {
        $this->recalculate();
    }

    public function recalculate(): void
    {
        $this->subtotal = array_sum(array_column($this->cart, 'subtotal'));
        $afterDiscount = max(0, $this->subtotal - $this->discount);
        $this->taxAmount = $afterDiscount * ($this->taxRate / 100);
        $this->total = $afterDiscount + $this->taxAmount;
    }

    public function searchByBarcode(string $barcode): void
    {
        $medicine = Medicine::where('barcode', $barcode)->first();
        if ($medicine) {
            $this->addToCart($medicine->id);
            $this->search = '';
        }
    }

    public function printLastSale(): void
    {
        if ($this->lastSaleId) {
            $this->js('window.open("' . route('invoice.print', ['sale' => $this->lastSaleId]) . '", "_blank")');
        } else {
            Notification::make()
                ->title(__('pos.no_sale_yet'))
                ->warning()
                ->send();
        }
    }

    public function checkout(): void
    {
        if (empty($this->cart)) {
            return;
        }

        // التعامل مع العميل
        $customerId = $this->customerId;

        if ($this->paymentMethod === 'credit') {
            if ($this->customerType === 'new') {
                $this->validate([
                    'newCustomer.name' => 'required|string|max:255',
                ]);

                $newCustomer = Customer::create([
                    'name' => $this->newCustomer['name'],
                    'phone' => $this->newCustomer['phone'] ?: null,
                    'balance' => 0,
                ]);

                $customerId = $newCustomer->id;
            }

            if (!$customerId) {
                Notification::make()
                    ->title(__('pos.credit_requires_customer'))
                    ->danger()
                    ->send();
                return;
            }
        }

        $prefix = Setting::get('invoice_prefix', 'INV-');
        $invoiceNumber = $prefix . strtoupper(uniqid());

        $sale = Sale::create([
            'invoice_number' => $invoiceNumber,
            'customer_id' => $customerId,
            'subtotal' => $this->subtotal,
            'discount' => $this->discount,
            'total' => $this->total,
            'profit' => 0,
            'payment_method' => $this->paymentMethod,
        ]);

        $profit = 0;

        foreach ($this->cart as $item) {
            SaleItem::create([
                'sale_id' => $sale->id,
                'medicine_id' => $item['id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'cost' => $item['cost'],
                'subtotal' => $item['subtotal'],
            ]);

            $profit += ($item['price'] - $item['cost']) * $item['quantity'];
            Medicine::where('id', $item['id'])->decrement('stock', $item['quantity']);
        }

        $sale->update(['profit' => $profit]);

        // إضافة الدين لرصيد العميل
        if ($this->paymentMethod === 'credit' && $customerId) {
            Customer::where('id', $customerId)->increment('balance', $this->total);
        }

        $this->lastSaleId = $sale->id;

        $this->cart = [];
        $this->discount = 0;
        $this->subtotal = 0;
        $this->taxAmount = 0;
        $this->total = 0;
        $this->customerId = null;
        $this->customerType = 'existing';
        $this->newCustomer = ['name' => '', 'phone' => ''];
        $this->paymentMethod = 'cash';

        $this->loadMedicines();
        $this->loadCustomers();

        Notification::make()
            ->title(__('pos.sale_completed'))
            ->success()
            ->send();

       
    }
}