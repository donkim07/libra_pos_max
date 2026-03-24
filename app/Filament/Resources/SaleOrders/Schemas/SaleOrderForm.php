<?php

namespace App\Filament\Resources\SaleOrders\Schemas;

use App\Models\Item;
use App\Models\SaleOrder;
use App\Models\Store;
use App\Models\Account;
use App\Models\Customer;
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
use Filament\Forms\Components\Repeater\TableColumn;

class SaleOrderForm
{
public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Customer & Payment Information')
                    ->schema([
                        Select::make('customer_id')
                            ->label('Customer')
                            ->required()
                            ->options(fn () => Customer::pluck('name', 'id'))
                            ->preload()
                            ->searchable()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('email')
                                    ->label('Email address')
                                    ->email()
                                    ->unique(Customer::class, 'email', ignoreRecord: true),
                                TextInput::make('phone')
                                    ->tel(),
                                TextInput::make('address')
                                    ->columnSpanFull(),
                            ])
                            ->createOptionUsing(function (array $data): int {
                                $customer = Customer::create([
                                    ...$data,
                                    'created_by' => Auth::id(),
                                    'updated_by' => Auth::id(),
                                ]);
                                return $customer->getKey();
                            })
                            ->createOptionAction(fn (Action $action) => $action
                                ->label('Add new customer')
                                ->icon('heroicon-o-user-plus')
                            ),

                        Select::make('account_id')
                            ->label('Account')
                            ->options(function () {
                                return Account::query()
                                    ->get()
                                    ->mapWithKeys(function ($account) {
                                        $balanceText = $account->balance > 0
                                            ? " | {$account->balance}"
                                            : " (No Balance)";
                                        return [$account->id => $account->name . $balanceText];
                                    });
                            })
                            ->searchable()
                            ->required(),

                        DatePicker::make('order_date')
                            ->label('Order Date')
                            ->native(false)
                            ->default(now()),

                        DatePicker::make('expected_delivery_date')
                            ->label('Expected Delivery Date')
                            ->native(false)
                            ->after('order_date'),

                        TextInput::make('receipt_number')
                            ->label('Receipt Number')
                            ->required()
                            ->dehydrated()
                            ->unique(ignoreRecord: true)
                            ->default(function (string $operation) {
                                return self::generateReceiptNumber();
                            })
                            ->belowContent([
                                Action::make('generate')
                                    ->label('Regenerate')
                                    ->icon(Heroicon::Sparkles)
                                    ->iconPosition(IconPosition::Before)
                                    ->visible(fn (string $operation) => $operation === 'create')
                                    ->action(function (TextInput $component) {
                                        $component->state(self::generateReceiptNumber());
                                    }),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->columns(4),

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
                            }),

                        TextInput::make('quantity_on_hand')
                            ->label('Qty on Hand')
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
                            ->minValue(1)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateItemTotal($set, $get)),

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

                Section::make('Payment Summary')
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
                            ->default(0)
                            ->dehydrated(),

                        TextInput::make('paid_amount')
                            ->label('Paid Amount (Advance/Partial)')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->default(0)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::updatePaymentStatus($set, $get)),
                    ])
                    ->columns(2),
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
        $discount = (float) ($get('discount') ?? 0);

        $total = max(($quantity * $price) - $discount, 0);
        $set('total', number_format($total, 2, '.', ''));
    }

    protected static function recalculateOrderTotal(array $items, Set $set, Get $get): void
    {
        $total = collect($items)->sum(fn ($item) => (float) ($item['total'] ?? 0));
        $formattedTotal = number_format($total, 2, '.', '');

        $set('total', $formattedTotal);
        self::updatePaymentStatus($set, $get);
    }

    protected static function updatePaymentStatus(Set $set, Get $get): void
    {
        $total = (float) ($get('../total') ?? 0);
        $paid = (float) ($get('paid_amount') ?? 0);

        if ($paid >= $total) {
            $set('payment_status', 'full_payment');
        } elseif ($paid > 0) {
            $set('payment_status', 'partial_payment');
        } else {
            $set('payment_status', 'unpaid');
        }
    }

    public static function generateReceiptNumber(): string
    {
        do {
            $number = 'SO-' . now()->format('Ymd') . '-' . strtoupper(str()->random(6));
        } while (
            SaleOrder::where('receipt_number', $number)->exists()
        );

        return $number;
    }
}
