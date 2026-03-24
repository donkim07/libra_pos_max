<?php

namespace App\Filament\Resources\BulkManufacturings\Pages;

use Filament\Actions;
use Filament\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use App\Services\BulkManufacturingService;
use Illuminate\Validation\ValidationException;
use App\Filament\Resources\BulkManufacturings\BulkManufacturingResource;

class EditBulkManufacturing extends EditRecord
{
    protected static string $resource = BulkManufacturingResource::class;

    public function mount(int | string $record): void
    {
        parent::mount($record);

        $record = $this->getRecord();

        $this->form->fill([
            'item_id' => $record->item_id,
            'date_manufactured' => $record->date_manufactured,
            'store_id' => $record->store_id,
            'quantity' => $record->quantity,
            'notes' => $record->notes,
            'remaining_quantity' => $record->remaining_quantity,
            'is_finished' => $record->is_finished,
            'waste_quantity' => $record->waste_quantity,
            'historical_ingredients' => $record->items->map(function ($item) {
                return [
                    'name' => $item->component?->name ?? '',
                    'quantity' => $item->quantity,
                    'unit_cost' => $item->unit_cost,
                    'total_cost' => $item->total_cost,
                ];
            })->toArray(),
            'existing_divisions' => $record->divisions->map(function ($div) {
                $perUnit = $div->quantity_produced > 0 ? $div->base_quantity_used / $div->quantity_produced : 0;
                return [
                    'target_item_id' => $div->target_item_id,
                    'paste_per_unit' => $perUnit,
                    'quantity_produced' => $div->quantity_produced,
                    'total_base_used' => $div->base_quantity_used,
                ];
            })->toArray(),
            'new_divisions' => [],
        ]);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['new_divisions'])) {
            $data['divisions'] = collect($data['new_divisions'])
                ->map(function ($div) {
                    $div['total_base_used'] = ((float) ($div['paste_per_unit'] ?? 0)) * ((float) ($div['quantity_produced'] ?? 0));
                    return $div;
                })
                ->filter(function ($div) {
                    return !empty($div['target_item_id']) && isset($div['quantity_produced']) && $div['quantity_produced'] > 0;
                })
                ->values()
                ->toArray();
            unset($data['new_divisions']);
        }

        $sumNewBase = collect($data['divisions'] ?? [])->sum('total_base_used');
        if ($sumNewBase > (float) $this->record->remaining_quantity) {
            throw ValidationException::withMessages([
                'new_divisions' => ['Total base used across new divisions exceeds the remaining bulk quantity.'],
            ]);
        }

        $data['remaining_quantity'] = (float) $this->record->remaining_quantity - $sumNewBase;
        $data['waste_quantity'] = 0;

        if (isset($data['is_finished']) && $data['is_finished'] && !$this->record->is_finished && $data['remaining_quantity'] > 0) {
            $data['waste_quantity'] = $data['remaining_quantity'];
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try {
            return app(BulkManufacturingService::class)->update($record, $data);
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Failed to update bulk manufacturing order')
                ->body($e->getMessage())
                ->send();

            throw $e;
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Bulk manufacturing order updated successfully.');
    }
}
