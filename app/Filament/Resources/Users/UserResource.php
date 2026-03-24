<?php

namespace App\Filament\Resources\Users;

use UnitEnum;
use BackedEnum;
use App\Models\User;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Actions\DeleteAction;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Clusters\Settings\Settings;
use Filament\Infolists\Components\TextEntry;
use Filament\Forms\Components\DateTimePicker;
use App\Filament\Resources\Users\Pages\ManageUsers;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class UserResource extends Resource
{

    use HasPageShield;
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Users;

    // protected static ?string $cluster = Settings::class;

    protected static string | UnitEnum | null $navigationGroup = 'Settings';
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
        ->schema([
            TextInput::make('name')
                ->required(),

            TextInput::make('email')
                ->label('Email address')
                ->email()
                ->required(),

            TextInput::make('password')
                ->password()
                ->required()
                ->visibleOn('create')           // usually only on create
                ->dehydrated(fn ($state) => filled($state)), // only save if filled

            Select::make('UserStore.name')
                ->label('Store')
                ->relationship('UserStore', 'name')
                ->searchable()
                ->preload()
                ->default(null),

            // ── Add this ────────────────────────────────────────────────
            Select::make('roles')
                ->relationship(
                        name: 'roles',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn ($query) =>
                            Auth::user()->hasRole('Super Admin')
                                ? $query  // super_admin sees everything
                                : $query->where('name', '!=', 'Super Admin')  // everyone else excludes it
                    )
                ->multiple()                        // allow many roles (common)
                ->preload()                         // loads options right away
                ->searchable()                      // nice when you have many roles
                ->label('Roles')
                // ->columnSpanFull()                  // looks better on full width
                // optional: restrict who can change roles
                ->visible(function ($record): bool {
                    $user = Auth::user();

                    return $user->hasRole('Super Admin')
                        || $user->hasRole('Admin')           // ← add this (or any other role)
                        || ($record && $record->id === $user->id);  // still allow self-edit
                }),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('email')
                    ->label('Email address'),
                TextEntry::make('email_verified_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('two_factor_secret')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('two_factor_recovery_codes')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('two_factor_confirmed_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('store_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('created_by')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('updated_by')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('roles.name')
                ->badge()
                ->getStateUsing(function ($record) {
                    $roles = $record->roles->pluck('name')->toArray();

                    // Optional: still hide 'super_admin' badge from non-super_admins
                    if (! Auth::user()->hasRole('Super Admin')) {
                        $roles = array_diff($roles, ['Super Admin']);
                    }

                    return $roles;
                }),
                TextColumn::make('creator.name')
                    ->numeric()
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
            ->filters([
                //
            ])
            ->recordActions([
                // ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(function (Builder $query): Builder {
                    if (! Auth::user()?->hasRole('Super Admin')) {
                        // Exclude users who have the 'super_admin' role
                        $query->whereDoesntHave('roles', function ($q) {
                            $q->where('name', 'Super Admin');
                        });
                    }

                    return $query;
                });
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageUsers::route('/'),
        ];
    }
}
