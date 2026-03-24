<?php

namespace App\Filament\Resources\TransferFunds;



use UnitEnum;
use BackedEnum;
use Filament\Forms;
use Filament\Tables;
use App\Models\Account;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Models\TransferFunds;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use App\Filament\Resources\TransferFunds\Pages;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use App\Filament\Resources\TransferFunds\Pages\CreateTransferFund;
use App\Filament\Resources\TransferFunds\Pages\CreateTransferFunds;
use App\Filament\Resources\TransferFunds\Pages\ManageTransferFunds;

class TransferFundsResource extends Resource
{

    use HasPageShield;
    protected static ?string $model = TransferFunds::class;

    protected static string | UnitEnum | null $navigationGroup = 'Accounting';

    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsUpDown;

    public static function form(Schema $schema): Schema
{
    return $schema
        ->components([
            Select::make('from_account_id')
                ->label('From Account')
                ->searchable()
                ->options(function () {
                    return Account::query()
                        ->get()
                        ->mapWithKeys(function ($account) {
                            $balanceText = $account->balance > 0
                                ? " | Bal: {$account->balance}"
                                : " (No Balance)";
                            return [$account->id => $account->name . $balanceText];
                        });
                })
                ->required()
                ->live()
                ->afterStateUpdated(function (Set $set) {
                    $set('to_account_id', null); // Reset destination when source changes
                }),

            Select::make('to_account_id')
                ->label('To Account')
                ->searchable()
                ->options(function (Get $get) {
                    $fromId = $get('from_account_id');
                    return Account::query()
                        ->when($fromId, fn($q) => $q->where('id', '!=', $fromId)) // Exclude same account
                        ->get()
                        ->mapWithKeys(function ($account) {
                            $balanceText = $account->balance > 0
                                ? " | Bal: {$account->balance}"
                                : " (No Balance)";
                            return [$account->id => $account->name . $balanceText];
                        });
                })
                ->required()
                ->rules([
                    fn (Get $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                        $from = $get('from_account_id');
                        if ($value && $from && $value == $from) {
                            $fail('Cannot transfer to the same account.');
                        }
                    },
                ]),

            TextInput::make('amount')
                ->label('Amount')
                ->required()
                ->prefix('TSH ')
                ->numeric()
                ->minValue(1)
                ->rules([
                    fn (Get $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                        $fromId = $get('from_account_id');
                        if (!$fromId) return;

                        $fromAccount = Account::find($fromId);
                        if (!$fromAccount) return;

                        $amount = (float) $value;
                        if ($amount > $fromAccount->balance) {
                            $fail("Amount ({$amount}) exceeds available balance ({$fromAccount->balance}) in {$fromAccount->name}.");
                        }
                    },
                ]),

            Textarea::make('note')
                ->label('Note')
                ->columnSpanFull(),

            Hidden::make('created_by')
                ->default(Auth::id()),
        ]);
}

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fromAccount.name')
                    ->label('From')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('toAccount.name')
                    ->label('To')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('amount')
                    ->money('TSH')
                    ->sortable(),

                TextColumn::make('note')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('creator.name')
                    ->label('Created By')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')

            ->filters([
                //
            ])
            ->actions([
                // EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageTransferFunds::route('/'),
        ];
    }
}
