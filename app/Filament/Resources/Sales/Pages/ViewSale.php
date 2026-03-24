<?php

namespace App\Filament\Resources\Sales\Pages;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\Sales\SaleResource;

class ViewSale extends ViewRecord
{
    protected static string $resource = SaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Action::make('print')
            //     ->label('Print Receipt')
            //     ->icon('heroicon-o-printer')
            //     ->color('success')
            //     ->action(fn () => $this->js('window.print()'))
            //     ->openUrlInNewTab(),

            Action::make('print')
                ->label('Print Receipt')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->url(fn () => route('sales.print', $this->record))
                ->openUrlInNewTab(),
                
            EditAction::make(),
        ];
    }
}
