<?php

namespace App\Filament\Resources\Expenses\Schemas;

use App\Models\Store;
use App\Models\Account;
use App\Models\Expense;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;

class ExpenseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section:: make([
                Select::make('title')
                    ->searchable()
                    ->options(fn () => Expense::pluck('title', 'title')->unique())
                    ->required(),
                Textarea::make('description')
                    ->default(null),
                TextInput::make('amount')
                    ->required()
                    ->prefix('TSH ')
                    ->numeric(),
                Select::make('account_id')
                    ->required()
                    ->searchable()
                    ->options(function () {
                                return Account::query()
                                    ->get()
                                    ->mapWithKeys(function ($account) {
                                        $balanceText = $account->balance > 0
                                            ? " | {$account->balance}"
                                            : " (No Balance)";
                                        return [$account->id => $account->name . $balanceText];
                                    });
                            }),

                Select::make('store_id')
                    ->searchable()
                    ->options(fn () => Store::pluck('name', 'id'))
                    ->required()
                    ->default(Auth::user()->store_id),
                DatePicker::make('incurred_on')
                    ->default(now())
                    ->required(),
                Hidden::make('created_by')
                    ->default(Auth::id()),
                Hidden::make('updated_by')
                    ->default(Auth::id()),

                ])
                ->columns(2)
                ->columnSpanFull(),

            ]);
    }
}
