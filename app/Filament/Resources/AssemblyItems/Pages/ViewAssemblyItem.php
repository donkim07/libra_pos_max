<?php

namespace App\Filament\Resources\AssemblyItems\Pages;

use App\Filament\Resources\AssemblyItems\AssemblyItemResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAssemblyItem extends ViewRecord
{
    protected static string $resource = AssemblyItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
