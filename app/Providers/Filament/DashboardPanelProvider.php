<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use Caresome\FilamentAuthDesigner\AuthDesignerPlugin;
use Caresome\FilamentAuthDesigner\Data\AuthPageConfig;
use Caresome\FilamentAuthDesigner\Enums\MediaPosition;
use Coolsam\Modules\ModulesPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Assets\Css;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use JeffersonGoncalves\Filament\Topbar\TopbarPlugin;
use MarcoGermani87\FilamentCaptcha\FilamentCaptcha;
use Modules\PengajuanDana\Filament\PengajuanDanaPlugin;
use Modules\Ticketing\Filament\TicketingPlugin;
use Modules\User\Filament\UserPlugin;

class DashboardPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        FilamentAsset::register([
            Css::make('filament-sticky', resource_path('css/filament-sticky.css'))
                ->relativePublicPath('css/filament-sticky.css'),
        ]);

        return $panel
            ->maxContentWidth('full')
            // ->simplePageMaxContentWidth(Width::ExtraSmall)
            ->favicon(asset('storage/assets/logo-agt.png'))
            ->default()
            ->id('dashboard')
            ->path('dashboard')
            ->login(Login::class)
            ->colors([
                'primary' => Color::rgb('32, 32, 108'),
                // kebaca RGB (2,2,78)
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->plugins([
                FilamentCaptcha::make(),
                ModulesPlugin::make(),
                UserPlugin::make(),
                TicketingPlugin::make(),
                PengajuanDanaPlugin::make(),
                // TopbarPlugin::make(),
                AuthDesignerPlugin::make()
                    ->login(fn (AuthPageConfig $config) => $config
                        ->usingPage(Login::class)
                        ->media(asset('storage/assets/login-bg.jpg'))
                        ->mediaPosition(MediaPosition::Left)
                    ),
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
            ]);
    }
}
