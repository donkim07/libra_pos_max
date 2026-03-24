<?php

namespace App\Filament\Resources\BulkManufacturings;


use BackedEnum;
use UnitEnum;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use App\Models\BulkManufacturing;
use Filament\Support\Icons\Heroicon;
use App\Filament\Resources\Manufacturings\Schemas\BulkManufacturingForm;
use App\Filament\Resources\BulkManufacturings\Pages\EditBulkManufacturing;
use App\Filament\Resources\BulkManufacturings\Pages\ListBulkManufacturings;
use App\Filament\Resources\BulkManufacturings\Pages\CreateBulkManufacturing;
use App\Filament\Resources\BulkManufacturings\Tables\BulkManufacturingsTable;

class BulkManufacturingResource extends Resource
{
    protected static ?string $model = BulkManufacturing::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;



    protected static ?int $navigationSort = 6;

    protected static string | UnitEnum | null $navigationGroup = 'Item/Inventory';


    public static function form(Schema $schema): Schema
    {
        return BulkManufacturingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BulkManufacturingsTable::configure($table);
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
            'index' => ListBulkManufacturings::route('/'),
            'create' => CreateBulkManufacturing::route('/create'),
            'edit' => EditBulkManufacturing::route('/{record}/edit'),
        ];
    }
}
