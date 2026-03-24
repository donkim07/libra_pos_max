<?php

namespace App\Filament\Resources\SaleOrders\Pages;

// namespace App\Filament\Resources\Sales\Pages;

use App\Models\Item;
use App\Models\Store;
use Filament\Actions;
use Filament\Forms\Form;
use App\Models\SaleOrder;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use App\Models\StockAdjustment;
use App\Traits\UpdatesItemStatus;
use Filament\Actions\DeleteAction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Validation\ValidationException;
use Filament\Forms\Components\Repeater\TableColumn;
use App\Filament\Resources\SaleOrders\SaleOrderResource;
use App\Filament\Resources\SaleOrders\Schemas\SaleOrderForm;

class EditSaleOrder extends EditRecord
{
    protected static string $resource = SaleOrderResource::class;

    use UpdatesItemStatus;

    // Store original paid amount to calculate the difference
    protected $originalPaidAmount = 0;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),

            Action::make('print')
                ->label('Print Order')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->url(fn () => route('sale-orders.print', $this->record))
                ->openUrlInNewTab(),

            Action::make('saveAndFulfill')
                ->label('Save & Fulfill')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->modalHeading('Full payment detected')
                ->modalDescription('Paid amount equals the order total. Do you want to fulfill (deliver) this order now? This will deduct stock and mark it as delivered/completed.')
                ->modalSubmitActionLabel('Yes, fulfill now')
                ->modalCancelActionLabel('No, just save')
                ->visible(function (): bool {
                    $total = $this->getCurrentItemsTotal();
                    $paid = $this->getCurrentPaidAmount();

                    return $total > 0
                        && $paid >= $total
                        && ($this->record?->status === 'pending')
                        && ($this->record?->delivery_status === 'pending');
                })
                ->action(function (): void {
                    $this->save();

                    try {
                        $this->fulfillOrder($this->record);

                        Notification::make()
                            ->title('Order saved and fulfilled')
                            ->body('Stock has been deducted and order marked as completed.')
                            ->success()
                            ->send();

                        $this->redirect($this->getResource()::getUrl('index'));
                    } catch (ValidationException $e) {
                        Notification::make()
                            ->title('Saved, but cannot fulfill order')
                            ->body(collect($e->errors())->flatten()->implode("\n"))
                            ->danger()
                            ->color('danger')
                            ->send();
                    }
                }),

            // Fulfill action - only shown when appropriate
            Action::make('fulfill')
                ->label('Fulfill Order')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->modalHeading('Fulfill Sale Order')
                ->modalDescription('This will deduct stock and mark the order as delivered/completed. Continue?')
                ->modalSubmitActionLabel('Yes, Fulfill')
                ->visible(fn (SaleOrder $record): bool =>
                    $record->status === 'pending' &&
                    $record->delivery_status === 'pending'
                )
                ->action(function () {
                    try {
                        $this->fulfillOrder($this->record);

                        Notification::make()
                            ->title('Order fulfilled successfully')
                            ->body('Stock has been deducted and order marked as completed.')
                            ->success()
                            ->send();

                        $this->redirect($this->getResource()::getUrl('index'));
                    } catch (ValidationException $e) {
                        // Show errors in Filament notification style
                        Notification::make()
                            ->title('Cannot fulfill order')
                            ->body(collect($e->errors())->flatten()->implode("\n"))
                            ->danger()
                            ->color('danger')
                            ->send();
                    }
                }),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Customer & Dates')
                    ->schema([
                        Select::make('customer_id')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('account_id')
                            ->relationship('account', 'name')
                            ->searchable()
                            ->preload(),

                        DatePicker::make('order_date')
                            ->required(),

                        DatePicker::make('expected_delivery_date')
                            ->label('Expected Delivery'),

                        Placeholder::make('receipt_number')
                            ->content(fn ($record) => $record->receipt_number),

                        Placeholder::make('Current Status')
                            ->content(fn (SaleOrder $record) => ucfirst($record->status) . ' / ' .
                                ucfirst($record->payment_status) . ' / ' .
                                ucfirst($record->delivery_status)),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),


                Repeater::make('items')
                    ->rules([
                        'array',
                        'min:1',
                    ])
                    ->table([
                        TableColumn::make('Item')
                            ->markAsRequired()
                            ->width('300px'),
                        TableColumn::make('Store')
                            ->markAsRequired()
                            ->width('190px'),
                        TableColumn::make('Qnty on hand')
                            ->wrapHeader()
                            ->width('90px'),
                        TableColumn::make('Quantity')
                            ->markAsRequired()
                            ->width('70px'),
                        TableColumn::make('Price')
                            ->width('120px'),
                        TableColumn::make('Discount')
                            ->width('110px'),
                        TableColumn::make('Total')
                            ->width('120px'),
                    ])
                    ->label('Order Items')
                    ->schema([
                        Select::make('item_id')
                            ->label('Item')
                            ->options(function () {
                                return Item::query()
                                    ->where('is_active', true)
                                    ->get()
                                    ->mapWithKeys(function ($item) {
                                        $qty = (int) $item->total_stock;
                                        $text = $qty > 0
                                            ? " (Total stock: {$qty})"
                                            : " (Out of stock)";
                                        return [$item->id => $item->name . $text];
                                    });
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(2)
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                if ($state) {
                                    $item = Item::find($state);
                                    if ($item) {
                                        $set('price', number_format($item->selling_price ?? 0, 2, '.', ''));
                                        self::updateAvailableQuantity($set, $get);
                                    }
                                } else {
                                    $set('price', 0);
                                    self::updateAvailableQuantity($set, $get);
                                }
                                self::recalculateItemTotal($set, $get);
                            }),

                        Select::make('store_id')
                            ->label('Store')
                            ->options(fn () => Store::pluck('name', 'id')->toArray())
                            ->searchable()
                            ->default(fn () => Auth::user()?->store_id)
                            ->required()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                self::updateAvailableQuantity($set, $get);
                                self::recalculateItemTotal($set, $get);
                                self::validateStock($set, $get);
                            }),

                        TextInput::make('quantity_on_hand')
                            ->label('Qty on Hand')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false),

                        Hidden::make('available_quantity')
                            ->default(0),

                        Hidden::make('stock_error')
                            ->default(''),

                        TextInput::make('quantity')
                            ->label('Quantity')
                            ->required()
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::recalculateItemTotal($set, $get);
                                self::validateStock($set, $get);
                            })
                            ->validationAttribute('Quantity')
                            ->helperText(fn (Get $get) => $get('stock_error') ?: null)
                            ->extraAttributes(fn (Get $get) => [
                                'class' => $get('stock_error') ? 'border-red-500' : ''
                            ]),

                        TextInput::make('price')
                            ->label('Price')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateItemTotal($set, $get)),

                        TextInput::make('discount')
                            ->label('Discount')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->step(0.01)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateItemTotal($set, $get)),

                        TextInput::make('total')
                            ->label('Total')
                            ->disabled()
                            ->dehydrated()
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(8)
                    ->columnSpanFull()
                    ->collapsible()
                    ->defaultItems(1)
                    ->minItems(1)
                    ->maxItems(100)
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                        self::recalculateOrderTotal($state, $set, $get);
                    })
                    ->deleteAction(
                        fn ($action) => $action->after(function ($livewire) {
                            $items = $livewire->data['items'] ?? [];
                            $total = collect($items)->sum(fn ($item) => (float) ($item['total'] ?? 0));
                            $formattedTotal = number_format($total, 2, '.', '');
                            $livewire->data['total'] = $formattedTotal;
                        })
                    ),

                Section::make('Payment')
                    ->schema([
                        Placeholder::make('total_display')
                            ->label('Grand Total')
                            ->content(fn ($get) => 'TZS ' . number_format(collect($get('items') ?? [])->sum('total'), 2)),

                        TextInput::make('paid_amount')
                            ->label('Paid Amount (Advance / Full)')
                            ->numeric()
                            ->prefix('TZS')
                            ->minValue(0)
                            ->dehydrateStateUsing(fn ($state) => (float) str_replace(',', '', (string) $state))
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $total = (float) collect($get('items') ?? [])->sum('total');
                                $paid = (float) $state;

                                if ($paid >= $total) {
                                    $set('payment_status', 'full_payment');
                                } elseif ($paid > 0) {
                                    $set('payment_status', 'partial_payment');
                                } else {
                                    $set('payment_status', 'unpaid');
                                }
                            }),

                        Placeholder::make('payment_status_display')
                            ->label('Payment Status')
                            ->content(fn ($get) => ucfirst($get('payment_status') ?? 'unpaid')),
                    ])
                    ->columns(2),
            ]);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Store the original paid amount for later comparison
        $this->originalPaidAmount = $this->record->paid_amount ?? 0;

        // Load items relationship data into repeater format
        $data['items'] = $this->record->saleOrderItems->map(function ($item) {
            // FIX 1: Fetch quantity_on_hand for each item on page load
            $itemModel = Item::find($item->item_id);
            $qtyOnHand = 0;
            if ($itemModel) {
                $qtyOnHand = $itemModel->getQuantityForStore($item->store_id);
            }

            return [
                'item_id'            => $item->item_id,
                'store_id'           => $item->store_id,
                'quantity'           => $item->quantity,
                'price'              => $item->price,
                'discount'           => $item->discount,
                'total'              => $item->total,
                'quantity_on_hand'   => $qtyOnHand,
                'available_quantity' => $qtyOnHand,
                'stock_error'        => '',
            ];
        })->toArray();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Recalculate total from items (stock is validated at fulfillment time)
        $itemsTotal = collect($data['items'] ?? [])->sum(fn ($item) => (float) ($item['total'] ?? 0));
        $data['total'] = $itemsTotal;

        // Normalize "1,000.00" style inputs before casting
        $data['paid_amount'] = (float) str_replace(',', '', (string) ($data['paid_amount'] ?? 0));
        $paid = (float) $data['paid_amount'];

        // Update statuses
        if ($paid >= $itemsTotal) {
            $data['payment_status'] = 'full_payment';
        } elseif ($paid > 0) {
            $data['payment_status'] = 'partial_payment';
        } else {
            $data['payment_status'] = 'unpaid';
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $order = $this->record;

        DB::transaction(function () use ($order) {
            // Sync items
            $order->saleOrderItems()->delete();

            foreach ($this->data['items'] ?? [] as $itemData) {
                $order->saleOrderItems()->create([
                    'item_id'   => $itemData['item_id'],
                    'store_id'  => $itemData['store_id'],
                    'quantity'  => $itemData['quantity'],
                    'price'     => $itemData['price'],
                    'discount'  => $itemData['discount'] ?? 0,
                    'total'     => $itemData['total'],
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);
            }

            // FIX 3: Update account balance - deduct original and add new amount
            $this->handleAccountBalance($order);
        });
    }

    public function fulfillOrder(SaleOrder $order): void
    {
        if ($order->delivery_status !== 'pending' || $order->status === 'completed') {
            return;
        }

        // Stock availability check (same logic as create)
        $grouped = [];
        foreach ($order->saleOrderItems as $orderItem) {
            $key = $orderItem->item_id . '-' . $orderItem->store_id;
            $grouped[$key] = ($grouped[$key] ?? 0) + $orderItem->quantity;
        }

        $errors = [];
        foreach ($grouped as $key => $totalRequested) {
            [$itemId, $storeId] = explode('-', $key);
            $item = Item::find($itemId);
            if ($item) {
                $available = $item->getQuantityForStore((int)$storeId);
                if ($totalRequested > $available) {
                    $storeName = $item->stores()->find($storeId)?->name ?? "Store ID {$storeId}";
                    $errors[] = "Item '{$item->name}' in {$storeName}: only {$available} available, requested {$totalRequested}";
                }
            }
        }

        if ($errors) {
            throw ValidationException::withMessages(['items' => implode("\n", $errors)]);
        }

        // Proceed with deduction
        foreach ($order->saleOrderItems as $orderItem) {
            $item = $orderItem->item;
            $storeId = $orderItem->store_id;

            $quantityBefore = $item->getQuantityForStore($storeId);
            $quantityAfter = max(0, $quantityBefore - $orderItem->quantity);

            $item->updateStockForStore($storeId, $quantityAfter);

            $totalStock = DB::table('item_store')
                ->where('item_id', $item->id)
                ->sum('quantity');

            $newStatus = $totalStock > 0 ? 'in_stock' : 'out_of_stock';
            if ($item->status !== $newStatus) {
                $item->update(['status' => $newStatus]);
            }

            StockAdjustment::create([
                'item_id'         => $item->id,
                'store_id'        => $storeId,
                // 'sale_order_id'   => $order->id, // assuming you added this column
                'type'            => 'decrease',
                'quantity_change' => -$orderItem->quantity,
                'quantity_before' => $quantityBefore,
                'quantity_after'  => $quantityAfter,
                'reason'          => 'Fulfillment of Sale Order #' . $order->id,
                'created_by'      => Auth::id(),
            ]);

            $this->updateItemStatus($item);
        }

        $order->update([
            'status'          => 'completed',
            'delivery_status' => 'delivered',
        ]);
    }

    // FIX 3: Improved account balance handling
    protected function handleAccountBalance(SaleOrder $order): void
    {
        if ($order->account_id) {
            $account = $order->account;
            $newPaidAmount = (float) ($order->paid_amount ?? 0);
            $originalAmount = (float) $this->originalPaidAmount;

            // Calculate the difference
            $difference = $newPaidAmount - $originalAmount;

            // Only update if there's a change
            if ($difference != 0) {
                if ($difference > 0) {
                    // Increased payment - add the difference
                    $account->increment('balance', $difference);
                } else {
                    // Decreased payment - subtract the difference (which is negative, so we use decrement with absolute value)
                    $account->decrement('balance', abs($difference));
                }
            }
        }
    }

    // FIX 2: Validate stock inline and set error message on the item
    protected static function validateStock(Set $set, Get $get): void
    {
        $itemId = $get('item_id');
        $storeId = $get('store_id');
        $requestedQty = (int) ($get('quantity') ?? 0);
        $availableQty = (int) ($get('available_quantity') ?? 0);

        if ($itemId && $storeId && $requestedQty > 0) {
            if ($requestedQty > $availableQty) {
                $item = Item::find($itemId);
                $storeName = $storeId ? (Store::find($storeId)?->name ?? "Store") : "Store";
                $itemName = $item?->name ?? "Item";

                $errorMessage = "Only {$availableQty} available in {$storeName}";
                $set('stock_error', $errorMessage);
            } else {
                $set('stock_error', '');
            }
        } else {
            $set('stock_error', '');
        }
    }

    // FIX 2: Validate all items before saving
    protected function validateStockBeforeSave(array $items): void
    {
        $errors = [];

        foreach ($items as $index => $itemData) {
            $itemId = $itemData['item_id'] ?? null;
            $storeId = $itemData['store_id'] ?? null;
            $requestedQty = (int) ($itemData['quantity'] ?? 0);

            if ($itemId && $storeId && $requestedQty > 0) {
                $item = Item::find($itemId);
                if ($item) {
                    $availableQty = $item->getQuantityForStore($storeId);

                    if ($requestedQty > $availableQty) {
                        $storeName = Store::find($storeId)?->name ?? "Store ID {$storeId}";
                        $errors[] = "Row " . ($index + 1) . " - {$item->name} in {$storeName}: only {$availableQty} available, requested {$requestedQty}";
                    }
                }
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages([
                'items' => $errors
            ]);
        }
    }

    protected static function recalculateSaleTotal(array $items, Set $set, Get $get): void
    {
        $total = collect($items)->sum(fn ($item) => (float) ($item['total'] ?? 0));
        $formattedTotal = number_format($total, 2, '.', '');

        $set('total', $formattedTotal);
    }

    protected static function updateAvailableQuantity(Set $set, Get $get): void
    {
        $itemId = $get('item_id');
        $storeId = $get('store_id');

        $qty = 0;
        if ($itemId && $storeId) {
            $item = Item::find($itemId);
            if ($item) {
                $qty = $item->getQuantityForStore($storeId);
            }
        }

        $set('quantity_on_hand', $qty);
        $set('available_quantity', $qty);

        // Also validate stock when quantity is updated
        self::validateStock($set, $get);
    }

    protected static function recalculateItemTotal(Set $set, Get $get): void
    {
        $quantity = (float) ($get('quantity') ?? 0);
        $price = (float) ($get('price') ?? 0);
        $discount = (float) ($get('discount') ?? 0);

        $total = max(($quantity * $price) - $discount, 0);
        $set('total', number_format($total, 2, '.', ''));
    }

    protected static function recalculateOrderTotal(array $items, Set $set, Get $get): void
    {
        $total = collect($items)->sum(fn ($item) => (float) ($item['total'] ?? 0));
        $formattedTotal = number_format($total, 2, '.', '');

        $set('total', $formattedTotal);
    }

    protected function getCurrentItemsTotal(): float
    {
        $items = $this->data['items'] ?? [];

        return (float) collect($items)->sum(fn ($item) => (float) ($item['total'] ?? 0));
    }

    protected function getCurrentPaidAmount(): float
    {
        $raw = $this->data['paid_amount'] ?? 0;

        return (float) str_replace(',', '', (string) $raw);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
