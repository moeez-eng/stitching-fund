<?php

namespace App\Providers\Filament;

use Filament\Pages;
use Filament\Panel;
use Filament\Widgets;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use App\Filament\Pages\Auth\Register;
use App\Http\Middleware\CheckUserStatus;
use Filament\Widgets\StatsOverviewWidget;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Filament\Http\Middleware\Authenticate as FilamentAuthenticate;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->registration(Register::class)
            ->brandName('Lotrix')
            ->brandLogo(asset('images/logo-removebg.png'))
            ->brandLogoHeight('3.5rem')
            ->databaseNotifications()
            ->databaseNotificationsPolling('15s')
            ->renderHook(
                'panels::head.end',
                fn (): string => '
                    <meta name="csrf-token" content="{{ csrf_token() }}">
                    <link rel="icon" type="image/png" href="' . asset('images/logo-removebg.png') . '">
                    <style>
                        .filament-branding img {
                            border-radius: 12px !important;
                            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1) !important;
                            max-width: 80px !important;
                            height: auto !important;
                        }
                    </style>
                ',
            )
            ->pages([
                \Filament\Pages\Dashboard::class,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverResources(in: app_path('Filament/Resources/UserInvitation'), for: 'App\\Filament\\Resources\\UserInvitation')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->colors([
                'primary' => Color::Purple,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
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
                FilamentAuthenticate::class,
                CheckUserStatus::class,
            ]);
    }
}
