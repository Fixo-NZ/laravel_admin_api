<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Resources;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('/')
            ->login()
            ->colors([
                'primary' => Color::hex('#4D47C3'), // Custom primary color (orange)
            ])
            ->discoverResources(
                in: app_path('Filament/Resources'),
                for: 'App\\Filament\\Resources'
            )
            ->discoverPages(
                in: app_path('Filament/Admin/Pages'),
                for: 'App\\Filament\\Admin\\Pages'
            )
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(
                in: app_path('Filament/Admin/Widgets'),
                for: 'App\\Filament\\Admin\\Widgets'
            )
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
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

            // ================================================================
            // NON-COLLAPSIBLE VERSION (Commented Out)
            // ================================================================
            // ->navigationGroups([
            //     \Filament\Navigation\NavigationGroup::make()
            //         ->label('User Overview')
            //         ->icon('heroicon-o-user-circle')
            //         ->collapsed(false)
            //         ->items([
            //             // Add your pages manually here if needed
            //         ]),
            // ])

            // ================================================================
            // COLLAPSIBLE VERSION (Default Active)
            // ================================================================
            ->navigationGroups([
                \Filament\Navigation\NavigationGroup::make('User Overview')
                    ->icon('heroicon-o-user-circle')
                    ->collapsible() // makes it expandable/collapsible
                    ->collapsed(),  // starts collapsed by default

                \Filament\Navigation\NavigationGroup::make('Job Oversight')
                    ->icon('heroicon-o-briefcase')
                    ->collapsible()
                    ->collapsed(),
            ])

            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}

/* =========================================================================
   NOTES
   =========================================================================
   1. You now have two versions:
      - The **non-collapsible** sidebar 
      - The **collapsible** sidebar (active by default)

   2. Filament automatically groups pages using:
         protected static ?string $navigationGroup = 'User Overview';
      inside each page class (e.g., AdminPage, HomeownersPage, etc.)

   3. Change ->collapsed() to ->collapsed(false)
      if you want the collapsible group to start open.

*/
