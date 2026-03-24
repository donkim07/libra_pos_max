@props(['filters'])

@php
    $startDate = $filters['startDate'] ?? null;
    $endDate = $filters['endDate'] ?? null;

    $start = $startDate ? \Carbon\Carbon::parse($startDate)->startOfDay() : now()->subMonth()->startOfDay();
    $end = $endDate ? \Carbon\Carbon::parse($endDate)->endOfDay() : now()->endOfDay();

    // Calculate all values
    $revenue = \App\Models\Sale::whereBetween('created_at', [$start, $end])->sum('total');
    $purchases = \App\Models\Purchase::whereBetween('created_at', [$start, $end])->sum('total');
    $manufacturing = \App\Models\Manufacturing::whereBetween('date_manufactured', [$start, $end])->sum('total_cost');
    $cogs = $purchases + $manufacturing;
    $grossProfit = $revenue - $cogs;
    $expenses = \App\Models\Expense::whereBetween('incurred_on', [$start, $end])->sum('amount');
    $netProfit = $grossProfit - $expenses;
@endphp

<x-filament::section>
    <x-slot name="heading">
        Financial Statement Summary
    </x-slot>

    <div class="space-y-4">
        {{-- Revenue Section --}}
        <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
            <div class="flex justify-between items-center">
                <span class="text-lg font-semibold text-gray-900 dark:text-white">Revenue</span>
                <span class="text-lg font-bold text-success-600">TZS {{ number_format($revenue, 0) }}</span>
            </div>
            <div class="ml-4 mt-2 text-sm text-gray-600 dark:text-gray-400">
                <div class="flex justify-between">
                    <span>Sales</span>
                    <span>TZS {{ number_format($revenue, 0) }}</span>
                </div>
            </div>
        </div>

        {{-- COGS Section --}}
        <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
            <div class="flex justify-between items-center">
                <span class="text-lg font-semibold text-gray-900 dark:text-white">Cost of Goods Sold</span>
                <span class="text-lg font-bold text-danger-600">TZS {{ number_format($cogs, 0) }}</span>
            </div>
            <div class="ml-4 mt-2 space-y-1 text-sm text-gray-600 dark:text-gray-400">
                <div class="flex justify-between">
                    <span>Purchases</span>
                    <span>TZS {{ number_format($purchases, 0) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Manufacturing Costs</span>
                    <span>TZS {{ number_format($manufacturing, 0) }}</span>
                </div>
            </div>
        </div>

        {{-- Gross Profit --}}
        <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
            <div class="flex justify-between items-center bg-primary-50 dark:bg-primary-900/20 p-3 rounded-lg">
                <span class="text-lg font-bold text-gray-900 dark:text-white">Gross Profit</span>
                <span class="text-lg font-bold {{ $grossProfit >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                    TZS {{ number_format($grossProfit, 0) }}
                </span>
            </div>
            @if($revenue > 0)
                <div class="ml-4 mt-2 text-sm text-gray-600 dark:text-gray-400">
                    <div class="flex justify-between">
                        <span>Gross Margin</span>
                        <span>{{ number_format(($grossProfit / $revenue) * 100, 1) }}%</span>
                    </div>
                </div>
            @endif
        </div>

        {{-- Operating Expenses --}}
        <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
            <div class="flex justify-between items-center">
                <span class="text-lg font-semibold text-gray-900 dark:text-white">Operating Expenses</span>
                <span class="text-lg font-bold text-warning-600">TZS {{ number_format($expenses, 0) }}</span>
            </div>
        </div>

        {{-- Net Profit/Loss --}}
        <div class="pt-2">
            <div class="flex justify-between items-center bg-{{ $netProfit >= 0 ? 'success' : 'danger' }}-50 dark:bg-{{ $netProfit >= 0 ? 'success' : 'danger' }}-900/20 p-4 rounded-lg border-2 border-{{ $netProfit >= 0 ? 'success' : 'danger' }}-500">
                <span class="text-xl font-bold text-gray-900 dark:text-white">Net Profit / Loss</span>
                <span class="text-xl font-bold {{ $netProfit >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                    TZS {{ number_format($netProfit, 0) }}
                </span>
            </div>
            @if($revenue > 0)
                <div class="ml-4 mt-2 text-sm text-gray-600 dark:text-gray-400">
                    <div class="flex justify-between">
                        <span>Net Margin</span>
                        <span>{{ number_format(($netProfit / $revenue) * 100, 1) }}%</span>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-filament::section>
