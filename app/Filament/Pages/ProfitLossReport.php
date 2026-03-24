<?php

namespace App\Filament\Pages;

use BackedEnum;
use UnitEnum;
use Filament\Pages\Page;
use Illuminate\Contracts\View\View;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Livewire\Attributes\Url;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;
use App\Filament\Widgets\ProfitLossStatsWidget;
use App\Filament\Widgets\ExpenseBreakdownTable;

class ProfitLossReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-scale';
    protected static string | UnitEnum | null $navigationGroup = 'Reports';
    protected static ?string $navigationLabel = 'Profit & Loss';

    protected string $view = 'filament.pages.profit-loss-report';

    #[Url]
    public ?array $filters = [];

    public function mount(): void
    {
        $this->filters['startDate'] = $this->filters['startDate'] ?? now()->subMonth()->format('Y-m-d');
        $this->filters['endDate']   = $this->filters['endDate']   ?? now()->format('Y-m-d');
        $this->form->fill($this->filters);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                DatePicker::make('startDate')
                    ->label('Start Date')
                    ->reactive()
                    ->afterStateUpdated(fn () => $this->refresh()),

                DatePicker::make('endDate')
                    ->label('End Date')
                    ->reactive()
                    ->afterStateUpdated(fn () => $this->refresh()),
            ])
            ->statePath('filters')
            ->columns(2);
    }

    public function refresh(): void
    {
        $this->dispatch('filtersUpdated');
    }

    public function getHeader(): ?View
    {
        return view('filament.pages.profit-loss-report-header');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ProfitLossStatsWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            // ExpenseBreakdownTable::class,
        ];
    }
}
