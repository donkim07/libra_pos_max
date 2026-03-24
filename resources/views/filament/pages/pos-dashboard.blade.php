<x-filament-panels::page full-width>  <!-- Or custom layout for no sidebar -->
<div class="flex">
    <input type="text" wire:model.live.onBlur.truee="search" name="customer" id="customer_id">
</div>
<div class="flex h-screen bg-gray-100 overflow-hidden">
    <!-- Left: Product Search/Grid (40-60%) -->
    <div class="w-3/5 p-4 overflow-y-auto">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search item or scan barcode..." class="w-full p-4 text-xl border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" autofocus />

        <div class="grid grid-cols-3 gap-4 mt-6">
            @foreach ($this->renderProducts() as $item)
                <button wire:click="addToCart({{ $item->id }})" class="p-4 bg-white rounded-lg shadow hover:shadow-lg text-center text-lg font-medium">
                    {{ $item->name }}<br>
                    <span class="text-green-600">TZS {{ number_format($item->selling_price) }}</span><br>
                    <small>Stock: {{ $item->getQuantityForStore($selectedStoreId) }}</small>
                </button>
            @endforeach
        </div>
    </div>

    <!-- Right: Cart & Totals (40%) -->
    <div class="w-2/5 bg-white p-6 shadow-lg flex flex-col">
        <h2 class="text-2xl font-bold mb-4">Cart</h2>

        <div class="flex-1 overflow-y-auto">
            @forelse ($cart as $itemId => $item)
                <div class="flex justify-between items-center py-3 border-b">
                    <div>
                        <p class="font-medium">{{ $item['name'] }}</p>
                        <p class="text-sm text-gray-600">TZS {{ number_format($item['price']) }} x {{ $item['qty'] }}</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <button wire:click="updateCartQty({{ $itemId }}, {{ $item['qty'] - 1 }})" class="px-3 py-1 bg-red-100 rounded">-</button>
                        <span class="text-xl">{{ $item['qty'] }}</span>
                        <button wire:click="updateCartQty({{ $itemId }}, {{ $item['qty'] + 1 }})" class="px-3 py-1 bg-green-100 rounded">+</button>
                        <span class="font-bold">TZS {{ number_format($item['total']) }}</span>
                    </div>
                </div>
            @empty
                <p class="text-center text-gray-500 mt-10">Cart empty – add items</p>
            @endforelse
        </div>

        <!-- Totals & Checkout -->
        <div class="mt-auto pt-6 border-t">
            <div class="text-3xl font-bold text-right">TZS {{ number_format($grandTotal) }}</div>
            <input wire:model="paidAmount" type="number" class="w-full p-4 mt-4 text-2xl border rounded-lg" placeholder="Paid Amount" />
            <button wire:click="checkout" class="w-full mt-4 py-6 bg-green-600 text-white text-2xl font-bold rounded-lg hover:bg-green-700 disabled:opacity-50" {{ empty($cart) ? 'disabled' : '' }}>
                Checkout
            </button>
        </div>
    </div>
</div>

@script
<script>
    // Optional: Barcode scanner focus (scanners input fast)
    document.addEventListener('keydown', (e) => {
        if (document.activeElement.tagName !== 'INPUT') {
            document.querySelector('input[wire\\:model="search"]').focus();
        }
    });
</script>
@endscript

</x-filament-panels::page>
