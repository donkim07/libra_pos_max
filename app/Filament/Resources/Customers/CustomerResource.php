<?php

namespace App\Filament\Resources\Customers;

use UnitEnum;
use BackedEnum;
use App\Models\User;
use App\Models\Customer;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Actions\DeleteAction;
use Filament\Tables\Filters\Filter;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Hidden;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use App\Filament\Resources\Customers\Pages\ManageCustomers;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

        protected static string | UnitEnum | null $navigationGroup = 'Sales';

        protected static ?int $navigationSort = 4;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->default(null),
                TextInput::make('phone')
                    ->tel()
                    ->default(null),
                Textarea::make('address')
                    ->default(null)
                    ->columnSpanFull(),
                Hidden::make('created_by')
                    ->default(Auth::id()),
                Hidden::make('updated_by')
                    ->default(Auth::id()),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('sales_sum_paid_amount')
                    ->label('Total Purchased')
                    ->prefix('TSH ')
                    ->sum('sales', 'paid_amount')

                    ->default(0)
                    ->formatStateUsing(fn ($state) => number_format($state))
                    ->sortable(),

                TextColumn::make('creator.name')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')

            ->filters([

    // 📅 Created date range
    Filter::make('created_at')
        ->form([
            DatePicker::make('from')
                ->label('From'),
            DatePicker::make('until')
                ->label('Until'),
        ])
        ->query(function (Builder $query, array $data) {
            return $query
                ->when(
                    $data['from'] ?? null,
                    fn ($query, $date) => $query->whereDate('created_at', '>=', $date)
                )
                ->when(
                    $data['until'] ?? null,
                    fn ($query, $date) => $query->whereDate('created_at', '<=', $date)
                );
        }),

    // 🧑‍💼 Created by (User)
    SelectFilter::make('created_by')
        ->label('Created By')
        ->options(fn () => User::pluck('name', 'id')->toArray())
        ->searchable(),

    // 💰 Total purchased range
    Filter::make('total_purchased')
        ->label('Total Purchased (TSH)')
        ->form([
            TextInput::make('min')
                ->numeric()
                ->label('Min'),
            TextInput::make('max')
                ->numeric()
                ->label('Max'),
        ])
        ->query(function (Builder $query, array $data) {
            return $query
                ->when(
                    $data['min'] ?? null,
                    fn ($query, $min) => $query->having('sales_sum_paid_amount', '>=', $min)
                )
                ->when(
                    $data['max'] ?? null,
                    fn ($query, $max) => $query->having('sales_sum_paid_amount', '<=', $max)
                );
        }),

    // 🛒 Customers with / without purchases
    SelectFilter::make('purchase_status')
        ->label('Purchase Status')
        ->options([
            'with_sales' => 'With Purchases',
            'no_sales' => 'No Purchases',
        ])
        ->query(function (Builder $query, array $data) {
            return match ($data['value'] ?? null) {
                'with_sales' => $query->has('sales'),
                'no_sales'   => $query->doesntHave('sales'),
                default      => $query,
            };
        }),

    // ⭐ Top customers shortcut
    Filter::make('top_customers')
        ->label('Top Customers')
        ->query(fn (Builder $query) =>
            $query->orderByDesc('sales_sum_paid_amount')
        ),

])
->filtersFormColumns(2)
->deferFilters(false)

            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCustomers::route('/'),
        ];
    }
}
