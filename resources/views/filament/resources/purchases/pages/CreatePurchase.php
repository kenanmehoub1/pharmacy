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

    // الأدوية
    public array $medicines = [];

    // المورد
    public ?int $supplierId = null;
    public array $suppliers = [];

    // الفاتورة
    public array $cart = [];
    public float $total = 0;
    public string $paymentMethod = 'cash';
    public string $invoiceNumber = '';
    public string $currency = 'EGP';
    public string $search = '';

    public function mount(): void
    {
        parent::mount();
        $this->invoiceNumber = \App\Models\Setting::get('invoice_prefix', 'PUR-') . strtoupper(uniqid());
        $this->currency = \App\Models\Setting::get('currency', 'EGP');
        $this->loadSuppliers();
        $this->loadMedicines();
    }

    public function loadSuppliers(): void
    {
        $this->suppliers = Supplier::query()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function loadMedicines(): void
    {
        $query = Medicine::query();

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
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
        if (!$medicine) {
            return;
        }

        if (isset($this->cart[$medicineId])) {
            $this->cart[$medicineId]['quantity']++;
            $this->cart[$medicineId]['subtotal'] =
                $this->cart[$medicineId]['quantity'] * $this->cart[$medicineId]['cost'];
        } else {
            $this->cart[$medicineId] = [
                'id' => $medicine->id,
                'name' => $medicine->name,
                'cost' => (float) $medicine->cost,
                'quantity' => 1,
                'subtotal' => (float) $medicine->cost,
            ];
        }

        $this->recalculate();
    }

    public function removeFromCart(int $medicineId): void
    {
        unset($this->cart[$medicineId]);
        $this->recalculate();
    }

    public function updatedCart(): void
    {
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

        if (!$this->supplierId) {
            Notification::make()
                ->title(__('purchase.select_supplier'))
                ->danger()
                ->send();
            return;
        }

        DB::transaction(function () {
            // إنشاء الفاتورة
            $purchase = Purchase::create([
                'invoice_number' => $this->invoiceNumber,
                'supplier_id' => $this->supplierId,
                'total' => $this->total,
                'payment_method' => $this->paymentMethod,
            ]);

            // إضافة العناصر + زيادة المخزون
            foreach ($this->cart as $item) {
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'medicine_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'cost' => $item['cost'],
                    'subtotal' => $item['subtotal'],
                ]);

                // زيادة المخزون
                Medicine::where('id', $item['id'])->increment('stock', $item['quantity']);

                // تحديث سعر التكلفة
                Medicine::where('id', $item['id'])->update(['cost' => $item['cost']]);
            }
        });

        Notification::make()
            ->title(__('purchase.created_successfully'))
            ->success()
            ->send();

        $this->redirect(PurchaseResource::getUrl('index'));
    }
}