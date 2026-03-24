<?php

namespace App\Filament\Resources\Manufacturings;

use Closure;
use UnitEnum;
use BackedEnum;
use Filament\Forms;
use App\Models\Item;
use Filament\Tables;
use App\Models\Store;
use Filament\Tables\Table;
// use Mockery\Matcher\Closure;
use Filament\Resources\Form;
use Filament\Schemas\Schema;
use App\Models\Manufacturing;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Cache;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Validation\ValidationException;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use App\Filament\Resources\Manufacturings\Pages\EditManufacturing;
use App\Filament\Resources\Manufacturings\Pages\ListManufacturings;
use App\Filament\Resources\Manufacturings\Pages\CreateManufacturing;
use App\Filament\Resources\Manufacturings\Schemas\ManufacturingForm;
use App\Filament\Resources\Manufacturings\Tables\ManufacturingsTable;
use App\Filament\Resources\Manufacturings\Schemas\ManufacturingInfolist;

class ManufacturingResource extends Resource
{
    protected static ?string $model = Manufacturing::class;

        use HasPageShield;

        protected static ?string $navigationLabel = 'Manufacturing';
    protected static ?string $pluralLabel = 'Manufacturings';
    protected static ?int $navigationSort = 3;


    protected static string | UnitEnum | null $navigationGroup = 'Item/Inventory';


    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

public static function form(Schema $schema): Schema
{
    return ManufacturingForm::configure($schema);
}

        public static function table(Table $table): Table
    {
        return ManufacturingsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ManufacturingInfolist::configure($schema);
    }

        public static function getPages(): array
    {
        return [
            'index' => ListManufacturings::route('/'),
            'create' => CreateManufacturing::route('/create'),
            'edit' => EditManufacturing::route('/{record}/edit'),
        ];
    }
}
