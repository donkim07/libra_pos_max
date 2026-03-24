<?php

namespace App\Providers\Filament;

use id;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Actions\Action;
use Filament\Pages\Dashboard;
// use App\Filament\Pages\Settings;
use Filament\Facades\Filament;
use Filament\Support\Assets\Js;
use Filament\Support\Enums\Width;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use App\Filament\Pages\PosDashboard;
use Illuminate\Support\Facades\Blade;
use Filament\Navigation\NavigationItem;
use Filament\Navigation\NavigationGroup;
use Filament\Widgets\FilamentInfoWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Filament\Http\Middleware\AuthenticateSession;
use App\Filament\Clusters\Settings\SettingsCluster;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use FilamentSpatieLaravelSettings\FilamentSpatieLaravelSettingsPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->login()
            ->registration()
            ->passwordReset()
            ->emailVerification()
            ->databaseNotifications()
            ->databaseNotificationsPolling('2s')
            ->sidebarCollapsibleOnDesktop()
            ->spa(hasPrefetching: true)
            ->unsavedChangesAlerts()
            ->colors([
                'primary' => Color::Blue,
            ])
            ->profile(isSimple: false)
            // ->userMenuItems([
            // Action::make('settings')
            //     ->url(fn (): string => Settings::getUrl())
            //     ->icon('heroicon-o-cog-6-tooth'),
            // ])
        // ->navigationItems([
        //     NavigationItem::make('Roles & Permissions')
        //         ->group('Settings')
        //         ->sort(30)
        //         ->icon('heroicon-o-lock-closed')
        //         ->url('admin/shield/roles'),
        // ])
            ->navigationGroups([
                NavigationGroup::make('Item/Inventory')
                ->icon('heroicon-o-archive-box')
                ->collapsed(),

                NavigationGroup::make('Sales')
                ->icon('heroicon-o-shopping-cart')
                ->collapsed(),

                NavigationGroup::make('Purchases')
                ->icon('heroicon-o-shopping-bag')
                ->collapsed(),

                NavigationGroup::make('Accounting')
                ->icon('heroicon-o-credit-card')
                ->collapsed(),

                NavigationGroup::make('Reports')
                ->icon('heroicon-o-chart-bar')
                ->collapsed(),

                NavigationGroup::make('Settings')
                ->icon('heroicon-o-cog-6-tooth')      // or heroicon-o-adjustments-horizontal
                ->collapsed(),                        // starts collapsed (dropdown)
                // ->collapsible(false)               // if you want it always expanded

            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                // Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
            ->widgets([
                // AccountWidget::class,
                // FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugins([
            FilamentShieldPlugin::make()
                ->navigationGroup('Settings')                  // string|Closure|null
                ->navigationSort(10),
            // FilamentSpatieLaravelSettingsPlugin::make()
            ])


            ->sidebarWidth('15rem')
        ->loginRouteSlug('login')
        ->registrationRouteSlug('register')
        ->passwordResetRoutePrefix('password-reset')
        ->passwordResetRequestRouteSlug('request')
        ->passwordResetRouteSlug('reset')
        ->emailVerificationRoutePrefix('email-verification')
        ->emailVerificationPromptRouteSlug('prompt')
        ->emailVerificationRouteSlug('verify')
        ->emailChangeVerificationRoutePrefix('email-change-verification')
        ->emailChangeVerificationRouteSlug('verify')


                ->renderHook(
            'panels::head.end',
            fn () => Blade::render(<<<'HTML'
                <script type="text/javascript">
                    (function(c,l,a,r,i,t,y){
                        c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
                        t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
                        y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
                    })(window, document, "clarity", "script", "vbl1z3yu8u");
                </script>
            HTML)
        );




            // ->clusters([]);



    }


    public function boot(): void
{
    // ...



    // ...
}
}
