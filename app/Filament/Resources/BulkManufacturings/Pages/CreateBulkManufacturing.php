<?php

namespace App\Filament\Resources\BulkManufacturings\Pages;

use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;
use App\Filament\Resources\BulkManufacturings\BulkManufacturingResource;

class CreateBulkManufacturing extends CreateRecord
{
    protected static string $resource = BulkManufacturingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (isset($data['ingredients'])) {
            $data['ingredients'] = collect($data['ingredients'])
                ->filter(function ($item) {
                    return !empty($item['item_id']) && isset($item['quantity']) && $item['quantity'] > 0;
                })
                ->values()
                ->toArray();
        }

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

        $sumBase = collect($data['divisions'] ?? [])->sum('total_base_used');
        if ($sumBase > (float) $data['quantity']) {
            throw ValidationException::withMessages([
                'new_divisions' => ['Total base used across divisions exceeds the produced bulk quantity.'],
            ]);
        }

        $data['remaining_quantity'] = (float) $data['quantity'] - $sumBase;
        $data['initial_remaining_quantity'] = (float) $data['quantity'];
        $data['is_finished'] = $data['is_finished'] ?? false;
        $data['waste_quantity'] = 0;

        if ($data['is_finished'] && $data['remaining_quantity'] > 0) {
            $data['waste_quantity'] = $data['remaining_quantity'];
        }

        $data['created_by'] = Auth::id();

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        try {
            return app(\App\Services\BulkManufacturingService::class)->create($data);
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Failed to create bulk manufacturing order')
                ->body($e->getMessage())
                ->send();

            throw $e;
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Bulk manufacturing order created successfully.');
    }
}
