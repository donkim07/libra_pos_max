<?php

namespace App\Filament\Resources\SaleOrders;

use App\Filament\Resources\SaleOrders\Pages\CreateSaleOrder;
use App\Filament\Resources\SaleOrders\Pages\EditSaleOrder;
use App\Filament\Resources\SaleOrders\Pages\ListSaleOrders;
use App\Filament\Resources\SaleOrders\Pages\ViewSaleOrder;
use App\Filament\Resources\SaleOrders\Schemas\SaleOrderForm;
use App\Filament\Resources\SaleOrders\Schemas\SaleOrderInfolist;
use App\Filament\Resources\SaleOrders\Tables\SaleOrdersTable;
use App\Models\SaleOrder;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SaleOrderResource extends Resource
{
    protected static ?string $model = SaleOrder::class;

        protected static string | UnitEnum | null $navigationGroup = 'Sales';

        protected static ?int $navigationSort = 3;


    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getNavigationBadge(): ?string
{
    return static::getModel()::where('status', 'pending')->count();
}

    public static function form(Schema $schema): Schema
    {
        return SaleOrderForm::configure($schema);
    }

    // public static function infolist(Schema $schema): Schema
    // {
    //     return SaleOrderInfolist::configure($schema);
    // }

    public static function table(Table $table): Table
    {
        return SaleOrdersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSaleOrders::route('/'),
            'create' => CreateSaleOrder::route('/create'),
            // 'view' => ViewSaleOrder::route('/{record}'),
            'edit' => EditSaleOrder::route('/{record}/edit'),
        ];
    }
}
