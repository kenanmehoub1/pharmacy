<?php

namespace App\Filament\Resources\Sales\Pages;

use App\Filament\Resources\Sales\SaleResource;
use App\Models\Customer;
use App\Models\Medicine;
use App\Models\Sale;
use App\Models\SaleItem;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditSale extends EditRecord
{
    protected static string $resource = SaleResource::class;

    protected string $view = 'filament.resources.sales.pages.edit-sale';

    public ?int $customerId = null;
    public array $customers = [];
    public string $customerType = 'existing';
    public array $newCustomer = ['name' => '', 'phone' => ''];

    public array $cart = [];
    public float $subtotal = 0;
    public float $discount = 0;
    public float $taxRate = 0;
    public float $taxAmount = 0;
    public float $total = 0;
    public string $paymentMethod = 'cash';
    public string $invoiceNumber = '';
    public string $currency = 'EGP';

    public string $search = '';
    public array $medicines = [];

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $sale = Sale::with('items.medicine')->findOrFail($record);

        $this->invoiceNumber = $sale->invoice_number;
        $this->customerId = $sale->customer_id;
        $this->discount = (float) $sale->discount;
        $this->paymentMethod = $sale->payment_method;
        $this->currency = \App\Models\Setting::get('currency', 'EGP');
        $this->taxRate = (float) \App\Models\Setting::get('tax_rate', 0);

        foreach ($sale->items as $item) {
            if ($item->medicine) {
                $this->cart[$item->medicine_id] = [
                    'id' => $item->medicine_id,
                    'name' => $item->medicine->name,
                    'price' => (float) $item->price,
                    'cost' => (float) $item->cost,
                    'quantity' => $item->quantity,
                    'subtotal' => (float) $item->subtotal,
                    'sale_item_id' => $item->id,
                    'stock' => $item->medicine->stock + $item->quantity,
                ];
            }
        }

        $this->recalculate();
        $this->loadCustomers();
        $this->loadMedicines();
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
                  ->orWhere('barcode', 'like', '%' . $this->search . '%')
                  ->orWhere('scientific_name', 'like', '%' . $this->search . '%');
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
            $this->cart[$medicineId]['quantity']++;
            $this->cart[$medicineId]['subtotal'] =
                $this->cart[$medicineId]['quantity'] * $this->cart[$medicineId]['price'];
        } else {
            $this->cart[$medicineId] = [
                'id' => $medicine->id,
                'name' => $medicine->name,
                'price' => (float) $medicine->price,
                'cost' => (float) $medicine->cost,
                'quantity' => 1,
                'subtotal' => (float) $medicine->price,
                'sale_item_id' => null,
                'stock' => $medicine->stock,
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

    public function saveSale(): void
    {
        if (empty($this->cart)) {
            Notification::make()
                ->title(__('sale.cart_empty'))
                ->danger()
                ->send();
            return;
        }

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
                    ->title(__('sale.credit_requires_customer'))
                    ->danger()
                    ->send();
                return;
            }
        }

        // احسب الدين القديم
        $oldSale = Sale::findOrFail($this->record->id);
        $oldCreditAmount = 0;
        $oldCreditCustomerId = null;

        if ($oldSale->payment_method === 'credit' && $oldSale->customer_id) {
            $oldCreditAmount = (float) $oldSale->total;
            $oldCreditCustomerId = $oldSale->customer_id;
        }

        DB::transaction(function () use ($customerId, $oldCreditAmount, $oldCreditCustomerId) {
            $sale = Sale::findOrFail($this->record->id);

            // إعادة المخزون القديم
            foreach ($sale->items as $item) {
                Medicine::where('id', $item->medicine_id)->increment('stock', $item->quantity);
            }

            $sale->items()->delete();

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

            $sale->update([
                'customer_id' => $customerId,
                'subtotal' => $this->subtotal,
                'discount' => $this->discount,
                'total' => $this->total,
                'profit' => $profit,
                'payment_method' => $this->paymentMethod,
            ]);

            // اخصم الدين القديم
            if ($oldCreditAmount > 0 && $oldCreditCustomerId) {
                Customer::where('id', $oldCreditCustomerId)->decrement('balance', $oldCreditAmount);
            }

            // ثم أضف الدين الجديد
            if ($this->paymentMethod === 'credit' && $customerId) {
                Customer::where('id', $customerId)->increment('balance', $this->total);
            }
        });

        Notification::make()
            ->title(__('sale.updated_successfully'))
            ->success()
            ->send();

        $this->redirect(SaleResource::getUrl('index'));
    }
}