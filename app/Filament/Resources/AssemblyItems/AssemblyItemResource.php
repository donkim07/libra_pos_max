<?php

namespace App\Filament\Resources\AssemblyItems;

use BackedEnum;
use UnitEnum;
use App\Models\Item;
use Filament\Tables\Table;
use App\Models\AssemblyItem;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\AssemblyItems\Pages\EditAssemblyItem;
use App\Filament\Resources\AssemblyItems\Pages\ViewAssemblyItem;
use App\Filament\Resources\AssemblyItems\Pages\ListAssemblyItems;
use App\Filament\Resources\AssemblyItems\Pages\CreateAssemblyItem;
use App\Filament\Resources\AssemblyItems\Schemas\AssemblyItemForm;
use App\Filament\Resources\AssemblyItems\Tables\AssemblyItemsTable;
use App\Filament\Resources\AssemblyItems\Schemas\AssemblyItemInfolist;

class AssemblyItemResource extends Resource
{
    protected static ?string $model = Item::class;
    protected static ?string $navigationLabel = 'Assemblies';
    protected static ?string $pluralLabel = 'Assemblies';
    protected static ?string $slug = 'assemblies';

    protected static ?int $navigationSort = 2;


    protected static string | UnitEnum | null $navigationGroup = 'Item/Inventory';


    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return AssemblyItemForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AssemblyItemInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AssemblyItemsTable::configure($table);
    }

    // public static function getRelations(): array
    // {
    // return [
    //     RelationManagers\BillOfMaterialRelationManager::class,
    // ];
    // }

    public static function getPages(): array
    {
        return [
            'index' => ListAssemblyItems::route('/'),
            'create' => CreateAssemblyItem::route('/create'),
            'view' => ViewAssemblyItem::route('/{record}'),
            'edit' => EditAssemblyItem::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }




}
