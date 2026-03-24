<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Livewire\Attributes\Url;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use App\Models\Store;
use App\Filament\Widgets\StockPerStoreTable;
use BackedEnum;
use UnitEnum;

class StockPerStoreReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-cube';
    protected static string | UnitEnum | null $navigationGroup = 'Reports';
    protected static ?string $navigationLabel = 'Stock per Store';

    protected string $view = 'filament.pages.stock-per-store-report';

    #[Url]
    public ?array $filters = [];

    public function mount(): void
    {
        $this->filters['storeId'] = $this->filters['storeId'] ?? null;
        $this->form->fill($this->filters);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('storeId')
                    ->label('Store')
                    ->options(Store::pluck('name', 'id')->prepend('All Stores', null))
                    ->live()
                    ->afterStateUpdated(fn () => $this->updateFilters()),
            ])
            ->statePath('filters');
    }

    public function updateFilters(): void
    {
        $this->dispatch('filtersUpdated');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            StockPerStoreTable::class,
        ];
    }
}
