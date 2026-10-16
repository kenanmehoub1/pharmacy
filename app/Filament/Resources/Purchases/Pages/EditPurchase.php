<?php

namespace App\Filament\Resources\Purchases\Pages;

use App\Filament\Resources\Purchases\PurchaseResource;
use App\Models\Medicine;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditPurchase extends EditRecord
{
    protected static string $resource = PurchaseResource::class;

    protected string $view = 'filament.resources.purchases.pages.edit-purchase';

    public ?int $supplierId = null;
    public array $suppliers = [];
    public string $supplierType = 'existing';
    public array $newSupplier = [
        'name' => '',
        'phone' => '',
        'address' => '',
    ];

    public array $cart = [];
    public float $total = 0;
    public string $paymentMethod = 'cash';
    public string $invoiceNumber = '';
    public string $currency = 'EGP';

    public ?int $editingMedicineId = null;

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

   public function mount(int|string $record): void
{
    parent::mount($record);

    $purchase = Purchase::with('items.medicine')->findOrFail($this->record->id);

    $this->invoiceNumber = $purchase->invoice_number;
    $this->supplierId = $purchase->supplier_id;
    $this->paymentMethod = $purchase->payment_method;
    $this->currency = \App\Models\Setting::get('currency', 'EGP');

    foreach ($purchase->items as $item) {
        $this->cart[$item->medicine_id] = [
            'id' => $item->medicine_id,
            'name' => $item->medicine->name ?? 'N/A',
            'cost' => (float) $item->cost,
            'quantity' => $item->quantity,
            'subtotal' => (float) $item->subtotal,
            'purchase_item_id' => $item->id,
        ];
    }

    $this->total = (float) $purchase->total;
    $this->loadSuppliers();
}

    public function loadSuppliers(): void
    {
        $this->suppliers = Supplier::query()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function loadMedicineToForm(int $medicineId): void
    {
        $medicine = Medicine::find($medicineId);
        if (!$medicine) {
            return;
        }

        $cartItem = $this->cart[$medicineId] ?? null;
        if (!$cartItem) {
            return;
        }

        $this->editingMedicineId = $medicineId;

        $this->newMedicine = [
            'name' => $medicine->name,
            'scientific_name' => $medicine->scientific_name ?? '',
            'company_name' => $medicine->company_name ?? '',
            'barcode' => $medicine->barcode ?? '',
            'category' => $medicine->category ?? '',
            'cost' => $cartItem['cost'],
            'price' => (float) $medicine->price,
            'quantity' => $cartItem['quantity'],
            'expiry_date' => $medicine->expiry_date ? $medicine->expiry_date->format('Y-m-d') : null,
            'min_stock' => $medicine->min_stock,
            'description' => $medicine->description ?? '',
        ];

        Notification::make()
            ->title(__('purchase.medicine_loaded'))
            ->info()
            ->send();
    }

    public function addToCart(): void
    {
        $this->validate([
            'newMedicine.name' => 'required|string|max:255',
            'newMedicine.cost' => 'required|numeric|min:0',
            'newMedicine.price' => 'required|numeric|min:0',
            'newMedicine.quantity' => 'required|integer|min:1',
        ]);

        if ($this->editingMedicineId) {
            $medicine = Medicine::find($this->editingMedicineId);
            if ($medicine) {
                $medicine->update([
                    'name' => $this->newMedicine['name'],
                    'scientific_name' => $this->newMedicine['scientific_name'] ?: null,
                    'company_name' => $this->newMedicine['company_name'] ?: null,
                    'barcode' => $this->newMedicine['barcode'] ?: null,
                    'category' => $this->newMedicine['category'] ?: null,
                    'price' => $this->newMedicine['price'],
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
                    'purchase_item_id' => $this->cart[$medicine->id]['purchase_item_id'] ?? null,
                ];

                $this->recalculate();
                $this->resetMedicineForm();

                Notification::make()
                    ->title(__('purchase.medicine_updated'))
                    ->success()
                    ->send();
            }
        } else {
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
                'purchase_item_id' => null,
            ];

            $this->recalculate();
            $this->resetMedicineForm();

            Notification::make()
                ->title(__('purchase.medicine_added'))
                ->success()
                ->send();
        }
    }

    protected function resetMedicineForm(): void
    {
        $this->editingMedicineId = null;
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
    }

    public function removeFromCart(int $medicineId): void
    {
        if (isset($this->cart[$medicineId]['purchase_item_id'])) {
            $item = PurchaseItem::find($this->cart[$medicineId]['purchase_item_id']);
            if ($item) {
                Medicine::where('id', $medicineId)->decrement('stock', $item->quantity);
                $item->delete();
            }
        } else {
            Medicine::where('id', $medicineId)->delete();
        }

        unset($this->cart[$medicineId]);
        $this->recalculate();
    }

    public function recalculate(): void
    {
        $this->total = array_sum(array_column($this->cart, 'subtotal'));
    }

    public function savePurchase(): void
    {
        if (empty($this->cart)) {
            Notification::make()
                ->title(__('purchase.cart_empty'))
                ->danger()
                ->send();
            return;
        }

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
            $purchase = Purchase::findOrFail($this->record->id);

            $purchase->update([
                'supplier_id' => $supplierId,
                'total' => $this->total,
                'payment_method' => $this->paymentMethod,
            ]);

            $purchase->items()->delete();

            foreach ($this->cart as $item) {
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'medicine_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'cost' => $item['cost'],
                    'subtotal' => $item['subtotal'],
                ]);
            }
        });

        Notification::make()
            ->title(__('purchase.updated_successfully'))
            ->success()
            ->send();

        $this->redirect(PurchaseResource::getUrl('index'));
    }
}