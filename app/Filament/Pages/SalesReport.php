<?php

namespace App\Filament\Pages;

use UnitEnum;
use BackedEnum;
use App\Models\Store;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Livewire\Attributes\Url;
use Illuminate\Contracts\View\View;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use App\Filament\Widgets\TopItemsTable;
use Filament\Schemas\Components\Section;
use App\Filament\Widgets\TotalSalesStats;
use Filament\Forms\Components\DatePicker;
use App\Filament\Widgets\SalesOverTimeChart;
use Filament\Forms\Concerns\InteractsWithForms;

class SalesReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-chart-bar';
    protected static string | UnitEnum | null $navigationGroup = 'Reports';
    protected static ?string $navigationLabel = 'Sales Report';
    protected string $view = 'filament.pages.sales-report';

    #[Url]
    public ?array $filters = [];

    public function mount(): void
    {
        $this->filters['startDate'] = $this->filters['startDate'] ?? now()->subDays(30)->format('Y-m-d');
        $this->filters['endDate'] = $this->filters['endDate'] ?? now()->format('Y-m-d');
        $this->filters['storeId'] = $this->filters['storeId'] ?? null;

        $this->form->fill($this->filters);
    }

    public function form(Schema $schema): Schema
    {
    return $schema
        ->schema([
                DatePicker::make('startDate')
                    ->label('Start Date')
                    ->live()
                    ->afterStateUpdated(fn () => $this->updateFilters()),

                DatePicker::make('endDate')
                    ->label('End Date')
                    ->live()
                    ->afterStateUpdated(fn () => $this->updateFilters()),

                // Select::make('storeId')
                //     ->label('Store')
                //     ->options(Store::pluck('name', 'id'))
                //     ->placeholder('All Stores')
                //     ->live()
                //     ->afterStateUpdated(fn () => $this->updateFilters()),
            ])
            ->statePath('filters')
            ->columns(2);

    }

    public function updateFilters(): void
    {
        // This triggers widget refresh
        $this->dispatch('filtersUpdated');
    }

    public function getHeader(): ?View
    {
        return view('filament.pages.sales-report-header');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            TotalSalesStats::class,
            SalesOverTimeChart::class,
            TopItemsTable::class,
        ];
    }
}
