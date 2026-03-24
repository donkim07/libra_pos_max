<?php

namespace App\Filament\Pages;

use UnitEnum;
use App\Models\Item;
use App\Models\Sale;
use App\Models\Store;
use App\Models\Customer;
use Filament\Forms\Form;
use Filament\Pages\Page;
use App\Models\SalesItem;
use Filament\Schemas\Schema;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use App\Models\StockAdjustment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use App\Services\PosService; // we'll create this
use App\Filament\Resources\Sales\Schemas\SaleForm;

class PosDashboard extends Page
{
    // protected static string | UnitEnum | null $navigationGroup = 'Sales';
    protected string $view = 'filament.pages.pos-dashboard';

    // Cart: array of [item_id => ['qty' => , 'price' => , 'discount' => , 'store_id' => , 'total' => ]]
    public array $cart = [];
    public float $grandTotal = 0;
    public float $paidAmount = 0;
    public string $search = '';
    public ?int $selectedStoreId = null;  // Default to user's store
    public ?int $customerId = null;
    public ?int $accountId = null;

    public function mount()
    {
        $this->selectedStoreId = Auth::user()?->store_id ?? Store::first()?->id;
        $this->paidAmount = 0; // Will sync to total
    }

    public function updatedSearch()
    {
        // Optional: if barcode scanned (long number), auto-add if matches barcode/SKU
        if (strlen($this->search) > 5) {  // Typical barcode length
            $item = Item::where('barcode', $this->search)
                ->orWhere('sku', $this->search)
                ->active()->first();
            if ($item) {
                $this->addToCart($item->id);
                $this->search = '';
            }
        }
    }

    public function addToCart($itemId)
    {
        $item = Item::findOrFail($itemId);
        $available = $item->getQuantityForStore($this->selectedStoreId);

        if ($available <= 0) {
            Notification::make()->danger()->title('Out of stock')->send();
            return;
        }

        // Check if already in cart
        if (isset($this->cart[$itemId])) {
            if ($this->cart[$itemId]['qty'] + 1 > $available) {
                Notification::make()->warning()->title('Max available reached')->send();
                return;
            }
            $this->cart[$itemId]['qty']++;
        } else {
            $this->cart[$itemId] = [
                'name' => $item->name,
                'qty' => 1,
                'price' => $item->selling_price,
                'discount' => 0,
                'store_id' => $this->selectedStoreId,
                'total' => $item->selling_price,
            ];
        }

        $this->recalculateTotals();
    }

    public function updateCartQty($itemId, $qty)
    {
        if (isset($this->cart[$itemId])) {
            $item = Item::find($itemId);
            $available = $item->getQuantityForStore($this->cart[$itemId]['store_id']);
            if ($qty > $available) {
                Notification::make()->warning()->title("Only {$available} available")->send();
                return;
            }
            if ($qty <= 0) {
                unset($this->cart[$itemId]);
            } else {
                $this->cart[$itemId]['qty'] = $qty;
                $this->cart[$itemId]['total'] = ($qty * $this->cart[$itemId]['price']) - $this->cart[$itemId]['discount'];
            }
            $this->recalculateTotals();
        }
    }

    // Similar for discount update, remove item...

    protected function recalculateTotals()
    {
        $this->grandTotal = collect($this->cart)->sum('total');
        $this->paidAmount = $this->grandTotal;  // Default full pay
    }

    public function checkout()
    {
        // Validate cart not empty, stock cumulatives (like your mutateFormDataBeforeCreate)
        // Customer/account required? Add checks

        $sale = Sale::create([
            'customer_id' => $this->customerId,
            'account_id' => $this->accountId,
            'receipt_number' => SaleForm::generateReceiptNumber(),  // Reuse your method
            'receipt_date' => now(),
            'total' => $this->grandTotal,
            'paid_amount' => $this->paidAmount,
            // status/payment_status like yours
        ]);

        foreach ($this->cart as $itemId => $data) {
            $sale->saleitems()->create([
                'item_id' => $itemId,
                'store_id' => $data['store_id'],
                'quantity' => $data['qty'],
                'price' => $data['price'],
                'discount' => $data['discount'],
                'total' => $data['total'],
            ]);

            // Stock adjustment like your handleInventory
            $item = Item::find($itemId);
            $qtyBefore = $item->getQuantityForStore($data['store_id']);
            $qtyAfter = $qtyBefore - $data['qty'];
            $item->updateStockForStore($data['store_id'], max(0, $qtyAfter));

            StockAdjustment::create([
                // like yours: decrease, sale_id, etc.
            ]);
        }

        // handleAccountBalance if paid >0

        Notification::make()->success()->title('Sale Completed')->send();
        $this->cart = [];  // Clear
        $this->recalculateTotals();
        // Optional: print receipt (JS window.print or thermal lib)
    }

    // Pagination/search for product grid if needed
    use WithPagination;
    public function renderProducts()
    {
        return Item::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->paginate(12);
    }



}
