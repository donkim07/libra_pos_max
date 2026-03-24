<?php

namespace App\Filament\Resources\Purchases\Schemas;

use App\Models\Item;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\Account;
use App\Models\PaymentMethod;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\IconPosition;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class PurchaseForm
{

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Supplier & Payment Information')
                    ->schema([
                        Select::make('supplier_id')
                            ->label('Supplier')
                            ->options(fn () => Supplier::pluck('name', 'id'))
                            ->searchable()
                            ->required(),

                        Select::make('account_id')
                            ->label('Payment Account')
                            ->options(function () {
                                return Account::query()
                                    ->get()
                                    ->mapWithKeys(function ($account) {
                                        $balanceText = $account->balance > 0
                                            ? " | Bal: {$account->balance}"
                                            : " (No Balance)";
                                        return [$account->id => $account->name . $balanceText];
                                    });
                            })
                            ->searchable()
                            ->required(),

                        // Hidden - will be auto-set based on paid_amount
                        Hidden::make('payment_method_id')
                            ->default(1), // Optional: fallback to first method if needed

                        Hidden::make('payment_status'),

                        DatePicker::make('purchase_date')
                            ->label('Purchase Date')
                            ->native(false)
                            ->default(now())
                            ->required(),

                        TextInput::make('reference_number')
                            ->label('Reference / Invoice #')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->default(fn () => 'PUR-' . now()->format('Ymd') . '-' . strtoupper(str()->random(4)))
                            ->belowContent([
                                Action::make('generate')
                                    ->label('Regenerate')
                                    ->icon(Heroicon::Sparkles)
                                    ->visible(fn (string $operation) => $operation === 'create')
                                    ->action(function (TextInput $component) {
                                        $component->state('PUR-' . now()->format('Ymd') . '-' . strtoupper(str()->random(4)));
                                    }),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->columns(3),

                Repeater::make('items')
                    ->label('Purchased Items')
                    ->schema([
                        // ... same as before ...
                        Select::make('item_id')
                            ->label('Item')
                            ->options(fn () => Item::pluck('name', 'id')->toArray())
                            ->searchable()
                            ->required()
                            ->columnSpan(2)
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                if ($state) {
                                    $item = Item::find($state);
                                    if ($item) {
                                        $set('price', number_format($item->cost_price ?? 0, 2, '.', ''));
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
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::updateAvailableQuantity($set, $get);
                                self::recalculateItemTotal($set, $get);
                            }),

                        TextInput::make('quantity_on_hand')
                            ->label('Current Stock')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false),

                        Hidden::make('available_quantity')
                            ->default(0),

                        TextInput::make('quantity')
                            ->label('Quantity')
                            ->required()
                            ->numeric()
                            ->default(1)
                            ->minValue(0.01)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateItemTotal($set, $get)),

                        TextInput::make('price')
                            ->label('Unit Price')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateItemTotal($set, $get)),

                        TextInput::make('total')
                            ->label('Line Total')
                            ->disabled()
                            ->dehydrated()
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(7)
                    ->columnSpanFull()
                    ->collapsible()
                    ->defaultItems(1)
                    ->minItems(1)
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                        self::recalculatePurchaseTotal($state, $set, $get);
                    })
                    ->deleteAction(
                        fn ($action) => $action->after(function ($livewire) {
                            $items = $livewire->data['items'] ?? [];
                            $total = collect($items)->sum(fn ($item) => (float) ($item['total'] ?? 0));
                            $livewire->data['total'] = number_format($total, 2, '.', '');
                            $livewire->data['paid_amount'] = $livewire->data['total'];
                        })
                    ),

                Section::make('Payment & Summary')
                    ->schema([
                        Placeholder::make('total_display')
                            ->label('Grand Total')
                            ->content(fn (Get $get): string =>
                                'TZS ' . number_format(
                                    collect($get('items') ?? [])->sum(fn ($item) => (float) ($item['total'] ?? 0)),
                                    2
                                )
                            ),

                        TextInput::make('total')
                            ->hidden()
                            ->dehydrated(),

                        TextInput::make('paid_amount')
                            ->label('Amount Paid')
                            ->numeric()
                            ->minValue(0)
                            ->default(fn (Get $get) => $get('total'))
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                $total = (float) $get('total');
                                $paid = (float) $get('paid_amount');

                                // Auto-set payment_status
                                if ($paid >= $total) {
                                    $set('payment_status', 'full_payment');
                                } elseif ($paid > 0) {
                                    $set('payment_status', 'partial_payment');
                                } else {
                                    $set('payment_status', 'unpaid');
                                }

                                $set('balance_due', max($total - $paid, 0));
                            }),

                        Placeholder::make('balance_due_display')
                            ->label('Balance Due')
                            ->content(fn (Get $get): string =>
                                'TZS ' . number_format((float) $get('total') - (float) $get('paid_amount'), 2)
                            ),

                        TextInput::make('discount')
                            ->label('Overall Discount')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),

                        Textarea::make('notes')
                            ->label('Notes')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),
            ]);
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
    }

    protected static function recalculateItemTotal(Set $set, Get $get): void
    {
        $quantity = (float) ($get('quantity') ?? 0);
        $price = (float) ($get('price') ?? 0);
        $total = $quantity * $price;
        $set('total', number_format($total, 2, '.', ''));
    }

    protected static function recalculatePurchaseTotal(array $items, Set $set, Get $get): void
    {
        $total = collect($items)->sum(fn ($item) => (float) ($item['total'] ?? 0));
        $formatted = number_format($total, 2, '.', '');
        $set('total', $formatted);
        $set('paid_amount', $formatted); // default paid = total
    }
}
