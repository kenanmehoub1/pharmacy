<?php

namespace App\Filament\Resources\Purchases\Pages;

use App\Filament\Resources\Purchases\PurchaseResource;
use App\Models\Medicine;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreatePurchase extends CreateRecord
{
    protected static string $resource = PurchaseResource::class;

    protected string $view = 'filament.resources.purchases.pages.create-purchase';

    // المورد
    public ?int $supplierId = null;
    public array $suppliers = [];
    public string $supplierType = 'existing';
    public array $newSupplier = [
        'name' => '',
        'phone' => '',
        'address' => '',
    ];

    // الفاتورة
    public array $cart = [];
    public float $total = 0;
    public string $paymentMethod = 'cash';
    public string $invoiceNumber = '';
    public string $currency = 'EGP';

    // الدواء الجديد
    public array $newMedicine = [
        'name' => '',
        'scientific_name' => '',
        'company_name' => '',
        'barcode' => '',
        'category' => '',
        'cost' => 0,
        'price' => 0,
        'quantity' => 1,
        'expiry_date' => null,
        'min_stock' => 10,
        'description' => '',
    ];

    public function mount(): void
    {
        parent::mount();
        $this->invoiceNumber = \App\Models\Setting::get('invoice_prefix', 'PUR-') . strtoupper(uniqid());
        $this->currency = \App\Models\Setting::get('currency', 'EGP');
        $this->loadSuppliers();
    }

    public function loadSuppliers(): void
    {
        $this->suppliers = Supplier::query()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function addToCart(): void
    {
        $this->validate([
            'newMedicine.name' => 'required|string|max:255',
            'newMedicine.cost' => 'required|numeric|min:0',
            'newMedicine.price' => 'required|numeric|min:0',
            'newMedicine.quantity' => 'required|integer|min:1',
        ]);

        $medicine = Medicine::create([
            'name' => $this->newMedicine['name'],
            'scientific_name' => $this->newMedicine['scientific_name'] ?: null,
            'company_name' => $this->newMedicine['company_name'] ?: null,
            'barcode' => $this->newMedicine['barcode'] ?: null,
            'category' => $this->newMedicine['category'] ?: null,
            'price' => $this->newMedicine['price'],
            'cost' => $this->newMedicine['cost'],
            'stock' => 0,
            'min_stock' => $this->newMedicine['min_stock'] ?: 10,
            'expiry_date' => $this->newMedicine['expiry_date'] ?: null,
            'description' => $this->newMedicine['description'] ?: null,
        ]);

        $quantity = (int) $this->newMedicine['quantity'];
        $cost = (float) $this->newMedicine['cost'];

        $this->cart[$medicine->id] = [
            'id' => $medicine->id,
            'name' => $medicine->name,
            'cost' => $cost,
            'quantity' => $quantity,
            'subtotal' => $quantity * $cost,
        ];

        $this->recalculate();

        // إعادة تعيين النموذج
        $this->newMedicine = [
            'name' => '',
            'scientific_name' => '',
            'company_name' => '',
            'barcode' => '',
            'category' => '',
            'cost' => 0,
            'price' => 0,
            'quantity' => 1,
            'expiry_date' => null,
            'min_stock' => 10,
            'description' => '',
        ];

        Notification::make()
            ->title(__('purchase.medicine_added'))
            ->success()
            ->send();
    }

    public function removeFromCart(int $medicineId): void
    {
        Medicine::where('id', $medicineId)->delete();
        unset($this->cart[$medicineId]);
        $this->recalculate();
    }

    public function recalculate(): void
    {
        $this->total = array_sum(array_column($this->cart, 'subtotal'));
    }

    public function save(): void
    {
        if (empty($this->cart)) {
            Notification::make()
                ->title(__('purchase.cart_empty'))
                ->danger()
                ->send();
            return;
        }

        // تحديد المورد
        $supplierId = $this->supplierId;

        if ($this->supplierType === 'new') {
            $this->validate([
                'newSupplier.name' => 'required|string|max:255',
            ]);

            $newSupplier = Supplier::create([
                'name' => $this->newSupplier['name'],
                'phone' => $this->newSupplier['phone'] ?: null,
                'address' => $this->newSupplier['address'] ?: null,
                'balance' => 0,
            ]);

            $supplierId = $newSupplier->id;
        }

        if (!$supplierId) {
            Notification::make()
                ->title(__('purchase.select_supplier'))
                ->danger()
                ->send();
            return;
        }

        DB::transaction(function () use ($supplierId) {
            $purchase = Purchase::create([
                'invoice_number' => $this->invoiceNumber,
                'supplier_id' => $supplierId,
                'total' => $this->total,
                'payment_method' => $this->paymentMethod,
            ]);

            foreach ($this->cart as $item) {
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'medicine_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'cost' => $item['cost'],
                    'subtotal' => $item['subtotal'],
                ]);

                Medicine::where('id', $item['id'])->increment('stock', $item['quantity']);
            }
        });

        Notification::make()
            ->title(__('purchase.created_successfully'))
            ->success()
            ->send();

        $this->redirect(PurchaseResource::getUrl('index'));
    }
}