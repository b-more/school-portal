<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;  // Your custom admin dashboard
use App\Filament\Pages\TeacherDashboard;
use App\Filament\Pages\TeacherProfile;
use App\Filament\Pages\MySchedule;
use App\Filament\Pages\MyReports;
use App\Filament\Pages\ParentDashboard;
use App\Filament\Pages\StudentDashboard;
use App\Filament\Pages\MarkAttendance;
use App\Filament\Pages\AttendanceReports;
use App\Filament\Pages\EnterResults;
use App\Filament\Pages\GenerateReportCards;
use App\Models\SchoolSettings;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Filament\Widgets\FeeGenerationWidget;
use App\Filament\Widgets\ParentHomeworkWidget;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Blue,
            ])
            ->favicon(function () {
                $settings = SchoolSettings::first();
                if ($settings && $settings->favicon) {
                    // Add cache-busting parameter based on update time
                    $cacheBuster = $settings->updated_at?->timestamp ?? time();
                    return asset('storage/' . $settings->favicon) . '?v=' . $cacheBuster;
                }
                return null;
            })
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->pages([
                // Register your custom admin dashboard
                Dashboard::class,
                // Register role-specific dashboards
                TeacherDashboard::class,
                TeacherProfile::class,
                MySchedule::class,
                MyReports::class,
                ParentDashboard::class,
                StudentDashboard::class,
                // Register custom pages (hidden from nav, accessed via buttons)
                MarkAttendance::class,
                AttendanceReports::class,
                // Results and Report Cards
                EnterResults::class,
                GenerateReportCards::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->authGuard('web');
    }
}
