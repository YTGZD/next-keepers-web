<?php

namespace App\Providers\Filament;

use App\Filament\Support\InitialsAvatarProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/**
 * The admin panel at /admin. Only users with role "admin" can sign in
 * (User::canAccessPanel); admins are created with `php artisan nk:admin`.
 * Login is rate-limited by Filament; there is no sign-up or password reset here.
 */
class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('Next Keepers Admin')
            ->defaultAvatarProvider(InitialsAvatarProvider::class)
            ->renderHook(PanelsRenderHook::FOOTER, fn (): HtmlString => self::versionFooter())
            ->colors([
                'primary' => Color::hex('#1B604A'),
                'warning' => Color::hex('#C99B43'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }

    /** Which commit is live, written by deploy/deploy.sh to the BUILD file. */
    public static function versionFooter(): HtmlString
    {
        $build = is_file(base_path('BUILD')) ? trim((string) file_get_contents(base_path('BUILD'))) : '';
        $text = $build !== '' ? 'Live version '.$build : 'Local copy (not deployed)';

        return new HtmlString('<p class="px-6 py-4 text-center text-xs text-gray-500">'.e($text).'</p>');
    }
}
