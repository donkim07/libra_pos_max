<?php

namespace App\Filament\Resources\Stores;

use UnitEnum;
use BackedEnum;
use App\Models\Store;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Actions\DeleteAction;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Components\Grid;
use Filament\Actions\DeleteBulkAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\IconPosition;
use App\Filament\Clusters\Settings\Settings;
use Filament\Infolists\Components\TextEntry;
use App\Filament\Clusters\Settings\Settingsr;
use App\Filament\Resources\Stores\Pages\ManageStores;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class StoreResource extends Resource
{
    protected static ?string $model = Store::class;

        use HasPageShield;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::BuildingStorefront;
// protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $recordTitleAttribute = 'name';
protected static string | UnitEnum | null $navigationGroup = 'Settings';
public static function getNavigationGroup(): string | null
{
    return 'Settings';
    // Or make it dynamic/translatable: return __('navigation.groups.settings');
}
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('address')
                    ->default(null),
                TextInput::make('phone')
                    ->tel()
                    ->default(null),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->default(null),
                Hidden::make('created_by')
                    ->default(Auth::id()),
                Hidden::make('updated_by')
                    ->default(Auth::id()),
            ]);
    }

  public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                // ── Hero / Main Info ──
                Section::make('Profile Information')
                    ->icon('heroicon-o-building-storefront') // change icon depending on entity: building-office, user-circle, truck, etc.
                    ->description('Basic details and contact information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Name')
                                    ->columnSpan(2)
                                    ->weight(FontWeight::Bold)
                                    ->size('lg')
                                    ->icon('heroicon-o-identification')
                                    ->iconPosition(IconPosition::Before),

                                TextEntry::make('email')
                                    ->label('Email Address')
                                    ->icon('heroicon-o-envelope')
                                    ->copyable()
                                    ->iconPosition(IconPosition::Before)
                                    ->color('primary')
                                    ->placeholder('No email provided'),

                                TextEntry::make('phone')
                                    ->label('Phone Number')
                                    ->icon('heroicon-o-phone')
                                    ->copyable()
                                    ->iconPosition(IconPosition::Before)
                                    ->placeholder('No phone provided'),

                                TextEntry::make('address')
                                    ->label('Physical Address')
                                    ->columnSpan(2)
                                    ->icon('heroicon-o-map-pin')
                                    ->prose()
                                    ->placeholder('No address provided'),
                            ]),
                    ])
                    ->collapsible(),

                // ── Additional Details (if you later add tin, website, contact person, etc.) ──
                // Section::make('Business Details')
                //     ->icon('heroicon-o-briefcase')
                //     ->collapsible()
                //     ->schema([...]),

                // ── Audit Trail ──
                Section::make('Activity History')
                    ->icon('heroicon-o-clock')
                    ->description('Creation and last update information')
                    // ->collapsed()
                    ->collapsible()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('creator.name')
                                    ->label('Created By')
                                    ->icon('heroicon-o-user')
                                    ->badge()
                                    ->color('success')
                                    ->default('System')
                                    ->placeholder('—'),

                                TextEntry::make('created_at')
                                    ->label('Created At')
                                    ->dateTime('M j, Y - g:i A')
                                    ->icon('heroicon-o-calendar-days'),

                                TextEntry::make('updated_at')
                                    ->label('Last Updated')
                                    ->dateTime('M j, Y - g:i A')
                                    ->icon('heroicon-o-arrow-path')
                                    ->placeholder('Never updated'),
                            ]),
                    ]),
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
                TextColumn::make('address')
                    ->searchable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('creator.name')
                    ->searchable()
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
                //
            ])
            ->recordActions([
                ViewAction::make(),
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
            'index' => ManageStores::route('/'),
        ];
    }
}
