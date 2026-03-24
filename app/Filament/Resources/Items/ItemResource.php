<?php

namespace App\Filament\Resources\Items;

use UnitEnum;
use BackedEnum;
use App\Models\Item;
use App\Models\Unit;
use App\Models\Store;
use App\Models\Category;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use TextEntry\TextEntrySize;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ImportAction;
use Illuminate\Support\Facades\DB;
use Filament\Actions\RestoreAction;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ReplicateAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Grid;
use App\Filament\Exports\ItemExporter;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ExportBulkAction;
use Filament\Schemas\Components\Group;
use Filament\Support\Enums\FontWeight;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\IconPosition;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use App\Filament\Resources\Items\Pages\ManageItems;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class ItemResource extends Resource
{
    protected static ?string $model = Item::class;

    // use HasPageShield;

    protected static ?string $navigationLabel = 'Inventory';
    protected static ?string $pluralLabel = 'Inventory Items';
    protected static ?string $slug = 'inventory-items';

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string | UnitEnum | null $navigationGroup = 'Item/Inventory';


    protected static ?string $recordTitleAttribute = 'name';

        public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('item_type', 'inventory');
    }



    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                Select::make('category_id')
                    ->label('Category')
                    ->searchable()
                    ->required()
                    ->options(fn () => Category::pluck('name', 'id')->toArray()),
                TextInput::make('sku')
                    ->label('SKU')
                    ->unique(ignoreRecord: true)
                    ->default(function (string $operation) {
                                return self::generateItemNumber();
                            })
                    ->belowContent([
                        Action::make('generate')
                            ->label('Regenerate')
                                    ->icon(Heroicon::Sparkles)
                                    ->iconPosition(IconPosition::Before)
                                    ->visible(fn (string $operation) => $operation === 'create')
                                    ->action(function (TextInput $component) {
                                        $component->state(self::generateItemNumber());
                                    }),
                    ]),
                Select::make('unit_id')
                    ->label('Unit')
                    ->searchable()
                    ->options(fn () => Unit::pluck('name', 'id')->toArray()),
                TextInput::make('cost_price')
                    ->required()
                    ->numeric()
                    ->prefix('TSH'),
                TextInput::make('selling_price')
                    ->required()
                    ->numeric()
                    ->prefix('TSH'),
                // TextInput::make('discount') // Uncomment if you want it back
                //     ->label('Discount Allowed')
                //     ->numeric()
                //     ->default(0),
                TextInput::make('quantity')
                    ->label('On Hand')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->disabled(fn (?Item $record) => $record !== null)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                        $qty = (float) ($state ?? 0);
                        $storeId = $get('store_id');

                        // Update status
                        $set('status', $qty > 0 ? 'in_stock' : 'out_of_stock');

                        // Update hidden store_quantities field
                        if ($storeId) {
                            $set('store_quantities', [$storeId => $qty]);
                        }
                    }),

                Hidden::make('store_quantities')
                    ->default(function (Get $get, ?Item $record) {
                        if ($record && $record->exists) {
                            // Editing: load existing quantity for this store
                            $storeId = $get('store_id');
                            if ($storeId) {
                                $storeData = $record->stores()
                                    ->where('store_id', $storeId)
                                    ->first();
                                return [$storeId => $storeData?->pivot?->quantity ?? 0];
                            }
                        }
                        // Creating: use the quantity input
                        $storeId = $get('store_id');
                        return $storeId ? [$storeId => $get('quantity') ?? 0] : [];
                    }),

                Hidden::make('status')
                    ->default('out_of_stock'),

                Toggle::make('is_active')
                    ->required()
                    ->default(true),
                Select::make('store_id')
                    ->label('Store')
                    ->searchable()
                    ->options(fn () => Store::pluck('name', 'id')->toArray())
                    ->default(fn () => Auth::user()?->store_id)
                    ->required(),
                Hidden::make('item_type')
                        ->default('inventory'),
                FileUpload::make('image')
                    ->image(),
            ]);

    }

public static function infolist(Schema $schema): Schema
{
    return $schema
        ->components([
            // Hero Section with Image and Key Info
            Flex::make([
                Section::make()
                    ->schema([
                        ImageEntry::make('image')
                            ->hiddenLabel()
                            ->height(150)
                            ->defaultImageUrl(url('images/items/icons-default-image.png'))
                            ->extraImgAttributes(['class' => 'rounded-lg object-cover']),
                    ])
                    ->grow(true),

                Section::make()
                    ->schema([
                        TextEntry::make('name')
                            // ->size(TextEntry\TextEntrySize::Large)
                            ->weight(FontWeight::Bold),
                        TextEntry::make('description')
                            ->prose()
                            ->markdown()
                            ->color('gray')
                            ->default('No description available'),
                        Group::make([
                            TextEntry::make('status')
                                ->badge()
                                ->color(fn (string $state): string => match ($state) {
                                    'in_stock' => 'success',
                                    'low_stock' => 'warning',
                                    'out_of_stock' => 'danger',
                                    default => 'gray',
                                }),
                            IconEntry::make('is_active')
                                ->label('Active')
                                ->boolean()
                                ->trueIcon('heroicon-o-check-badge')
                                ->falseIcon('heroicon-o-x-circle')
                                ->trueColor('success')
                                ->falseColor('danger'),
                        ])->columns(2),
                    ]),
            ])->from('lg'),

            // Product Details Section
            Section::make('Product Details')
                ->icon('heroicon-o-cube')
                ->description('Basic product information and categorization')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextEntry::make('category.name')
                                ->label('Category')
                                ->icon('heroicon-o-tag')
                                ->badge()
                                ->color('primary')
                                ->default('Uncategorized'),
                            TextEntry::make('sku')
                                ->label('SKU')
                                ->icon('heroicon-o-qr-code')
                                ->copyable()
                                ->default('N/A'),


                            TextEntry::make('barcode')
                                ->icon('heroicon-o-bars-3-bottom-left')
                                ->copyable()
                                ->default('N/A'),

                                TextEntry::make('unit.name')
                                ->label('Unit of Measure')
                                ->icon('heroicon-o-scale')
                                ->badge()
                                ->default('N/A'),
                        ]),

                ])
                // ->columns(2)
                ->collapsible(),

            // Pricing & Inventory Section
            Section::make('Pricing & Inventory')
                ->icon('heroicon-o-currency-dollar')
                ->description('Pricing information and stock levels')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextEntry::make('cost_price')
                                ->label('Cost Price')
                                ->money('TSH')
                                // ->size(TextEntry\TextEntrySize::Large)
                                ->weight(FontWeight::Bold)
                                ->color('success'),
                            TextEntry::make('selling_price')
                                ->label('Cost Price')
                                ->money('TSH')
                                // ->size(TextEntry\TextEntrySize::Large)
                                ->weight(FontWeight::Bold)
                                ->color('success'),

                            TextEntry::make('total_stock')
                                ->numeric(decimalPlaces: 0)
                                ->icon('heroicon-o-cube-transparent')
                                ->badge()
                                ->color(fn (int $state): string => match (true) {
                                    $state > 100 => 'success',
                                    $state > 20 => 'warning',
                                    default => 'danger',
                                }),
                            TextEntry::make('discount')
                                ->suffix('%')
                                ->numeric(decimalPlaces: 0)
                                ->color('warning')
                                ->icon('heroicon-o-receipt-percent')
                                ->default('No discount'),
                        ]),
                ])
                ->collapsible(),

            // Store Information
            Section::make('Store Information')
                ->icon('heroicon-o-building-storefront')
                ->schema([
                    TextEntry::make('store.name')
                        ->label('Store')
                        ->badge()
                        ->color('info')
                        ->default('N/A'),
                ])
                ->collapsible(),

            // Audit Trail Section (Collapsed by default)
            Section::make('Audit Trail')
                ->icon('heroicon-o-clock')
                ->description('Record creation and modification history')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextEntry::make('created_at')
                                ->label('Created')
                                ->dateTime('M j, Y - g:i A')
                                ->icon('heroicon-o-plus-circle')
                                ->default('N/A'),
                            TextEntry::make('updated_at')
                                ->label('Last Updated')
                                ->dateTime('M j, Y - g:i A')
                                ->icon('heroicon-o-arrow-path')
                                ->default('N/A'),
                            TextEntry::make('deleted_at')
                                ->label('Deleted')
                                ->dateTime('M j, Y - g:i A')
                                ->icon('heroicon-o-trash')
                                ->color('danger')
                                ->visible(fn (Item $record): bool => $record->trashed()),
                        ]),
                    Grid::make(2)
                        ->schema([
                            TextEntry::make('creator.name')
                                ->label('Created By')
                                ->icon('heroicon-o-user')
                                ->badge()
                                ->default('System'),
                            TextEntry::make('updator.name')
                                ->label('Updated By')
                                ->icon('heroicon-o-user')
                                ->badge()
                                ->default('N/A'),
                            TextEntry::make('deletor.name')
                                ->label('Deleted By')
                                ->icon('heroicon-o-user')
                                ->badge()
                                ->color('danger')
                                ->visible(fn (Item $record): bool => $record->trashed()),
                        ]),
                ])
                ->collapsed()
                ->collapsible(),
        ]);
}


protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
{
    return Item::query()
        ->where('item_type', 'inventory')
        ->select('items.*')
        ->selectSub(
            DB::table('item_store')
                ->selectRaw('COALESCE(SUM(quantity), 0)')
                ->whereColumn('item_store.item_id', 'items.id'),
            'total_stock'
        );
}

    public static function table(Table $table): Table
    {
        return $table
        ->striped()
        // ->deferLoading()
        // ->modifyQueryUsing(fn (Builder $query) => $query->where('item_type', 'inventory'))
        ->recordUrl(false)
            ->recordTitleAttribute('name')
            ->columns([
                ImageColumn::make('image') // Optional: add image preview in table
                    ->height(48)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->square(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('unit.name')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('category.name')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->sortable(),
                TextColumn::make('cost_price')
                    ->money('TZS')
                    ->sortable(),
                TextColumn::make('selling_price')
                    ->money('TZS')
                    ->sortable(),
                TextColumn::make('total_stock')
                    ->label('On Hand')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('item_type')
                    ->badge()
                    ->sortable(),
                TextColumn::make('status')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'in_stock'     => 'success',
                        'out_of_stock' => 'danger',
                        default        => 'gray',
                    }),
                TextColumn::make('store.name')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('creator.name')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updator.name')
                    ->label('Updated By')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deletor.name')
                    ->label('Deleted By')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->reorderableColumns()
            ->deferColumnManager(false)
            ->filters([

                SelectFilter::make('status')
                    ->options([
                        'in_stock'     => 'In Stock',
                        'out_of_stock' => 'Out of Stock',
                    ])
                    ->multiple(),
                TernaryFilter::make('is_active')
                    ->label('Active')
                    ->placeholder('Any')
                    ->trueLabel('Is active')
                    ->falseLabel('Is not active'),
                TrashedFilter::make(),

            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                    ForceDeleteAction::make(),
                    RestoreAction::make(),
                    ReplicateAction::make()
                        ->excludeAttributes(['sku', 'created_at', 'updated_at']),
                ])
                    ->label('')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size('sm')
                    ->color('primary')
                    ->button(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ExportBulkAction::make()
                        ->exporter(ItemExporter::class),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageItems::route('/'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }



        public static function generateItemNumber(): string
    {
        do {
            $number = 'SKU-' . now()->format('Ymd') . '-' . strtoupper(str()->random(6));
        } while (
            Item::where('sku', $number)->exists()
        );

        return $number;
    }
}
