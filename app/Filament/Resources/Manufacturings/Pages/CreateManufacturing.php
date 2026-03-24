<?php


namespace App\Filament\Resources\Manufacturings\Pages;

use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\Manufacturings\ManufacturingResource;
use App\Services\ManufacturingService;
use Illuminate\Database\Eloquent\Model;

class CreateManufacturing extends CreateRecord
{
    protected static string $resource = ManufacturingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Filter out deleted repeater items
        if (isset($data['ingredients'])) {
            $data['ingredients'] = collect($data['ingredients'])
                ->filter(function ($item) {
                    // Keep only items with valid item_id
                    return !empty($item['item_id']) &&
                           isset($item['quantity']) &&
                           $item['quantity'] > 0;
                })
                ->values()
                ->toArray();
        }

        $data['created_by'] = Auth::id();

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        // Use your service which handles everything
        try {
            return app(ManufacturingService::class)->create($data);
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Failed to create manufacturing order')
                ->body($e->getMessage())
                ->send();

            throw $e; // Re-throw to prevent navigation
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
            ->title('Manufacturing order created successfully.');
    }
}
