<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;  // Your custom admin dashboard
use App\Filament\Pages\TeacherDashboard;
use App\Filament\Pages\TeacherProfile;
use App\Filament\Pages\MySchedule;
use App\Filament\Pages\MyTeachingDocuments;
use App\Filament\Pages\MyReports;
use App\Filament\Pages\ParentDashboard;
use App\Filament\Pages\StudentDashboard;
use App\Filament\Pages\MarkAttendance;
use App\Filament\Pages\AttendanceReports;
use App\Filament\Pages\EnterResults;
use App\Filament\Pages\GenerateReportCards;
use App\Filament\Pages\ManageTimetable;
use App\Filament\Pages\TeacherSchedules;
use App\Filament\Pages\AccountsDashboard;
use App\Filament\Pages\FinancialReports;
use App\Models\SchoolSettings;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
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
        // St. Francis of Assisi School Corporate Colors
        // Navy Blue: #1e3a5f | Crimson Red: #dc2626

        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => [
                    50 => '239, 246, 255',   // Lightest
                    100 => '219, 234, 254',
                    200 => '191, 219, 254',
                    300 => '147, 197, 253',
                    400 => '96, 165, 250',
                    500 => '44, 82, 130',    // #2c5282 - Lighter navy
                    600 => '30, 58, 95',     // #1e3a5f - Main Navy
                    700 => '26, 50, 82',     // Darker
                    800 => '23, 44, 72',
                    900 => '20, 38, 62',
                    950 => '15, 28, 46',     // Darkest
                ],
                'danger' => [
                    50 => '254, 242, 242',
                    100 => '254, 226, 226',
                    200 => '254, 202, 202',
                    300 => '252, 165, 165',
                    400 => '248, 113, 113',
                    500 => '220, 38, 38',    // #dc2626 - School Red
                    600 => '185, 28, 28',    // #b91c1c
                    700 => '153, 27, 27',
                    800 => '127, 29, 29',
                    900 => '107, 26, 26',
                    950 => '69, 10, 10',
                ],
                'success' => [
                    50 => '240, 253, 244',
                    100 => '220, 252, 231',
                    200 => '187, 247, 208',
                    300 => '134, 239, 172',
                    400 => '74, 222, 128',
                    500 => '5, 150, 105',    // Emerald green
                    600 => '4, 120, 87',
                    700 => '3, 102, 74',
                    800 => '2, 84, 61',
                    900 => '2, 68, 50',
                    950 => '1, 42, 31',
                ],
                'warning' => [
                    50 => '255, 251, 235',
                    100 => '254, 243, 199',
                    200 => '253, 230, 138',
                    300 => '252, 211, 77',
                    400 => '251, 191, 36',
                    500 => '217, 119, 6',    // Amber
                    600 => '180, 83, 9',
                    700 => '146, 64, 14',
                    800 => '120, 53, 15',
                    900 => '99, 49, 18',
                    950 => '56, 28, 10',
                ],
                'info' => [
                    50 => '239, 246, 255',
                    100 => '219, 234, 254',
                    200 => '191, 219, 254',
                    300 => '147, 197, 253',
                    400 => '96, 165, 250',
                    500 => '59, 130, 246',
                    600 => '37, 99, 235',
                    700 => '29, 78, 216',
                    800 => '30, 64, 175',
                    900 => '30, 58, 138',
                    950 => '23, 37, 84',
                ],
                'gray' => [
                    50 => '249, 250, 251',
                    100 => '243, 244, 246',
                    200 => '229, 231, 235',
                    300 => '209, 213, 219',
                    400 => '156, 163, 175',
                    500 => '107, 114, 128',
                    600 => '75, 85, 99',
                    700 => '55, 65, 81',
                    800 => '31, 41, 55',
                    900 => '17, 24, 39',
                    950 => '3, 7, 18',
                ],
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
            ->discoverResources(in: app_path('Filament/Resources/Accounting'), for: 'App\\Filament\\Resources\\Accounting')
            ->pages([
                // Register your custom admin dashboard
                Dashboard::class,
                // Register role-specific dashboards
                TeacherDashboard::class,
                TeacherProfile::class,
                MySchedule::class,
                MyTeachingDocuments::class,
                MyReports::class,
                ParentDashboard::class,
                StudentDashboard::class,
                // Register custom pages (hidden from nav, accessed via buttons)
                MarkAttendance::class,
                AttendanceReports::class,
                // Results and Report Cards
                EnterResults::class,
                GenerateReportCards::class,
                // Timetable Management
                ManageTimetable::class,
                TeacherSchedules::class,
                // Accounts & Finance
                AccountsDashboard::class,
                FinancialReports::class,
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
            ->authGuard('web')
            ->brandName('St. Francis of Assisi')
            ->brandLogo(function () {
                $settings = SchoolSettings::first();
                if ($settings && $settings->school_logo) {
                    return asset('storage/' . $settings->school_logo);
                }
                return null;
            })
            ->brandLogoHeight('3rem')
            ->darkModeBrandLogo(function () {
                $settings = SchoolSettings::first();
                if ($settings && $settings->school_logo) {
                    return asset('storage/' . $settings->school_logo);
                }
                return null;
            })
            ->sidebarCollapsibleOnDesktop()
            ->databaseNotifications()
            ->userMenuItems([
                MenuItem::make()
                    ->label('Quick Guide')
                    ->url('/quick-guide')
                    ->icon('heroicon-o-book-open')
                    ->openUrlInNewTab(),
            ])
            ->renderHook(
                'panels::styles.after',
                fn () => new \Illuminate\Support\HtmlString('
                    <style>
                        /* =============================================
                           St. Francis of Assisi — Corporate Panel Theme
                           Navy: #1e3a5f  |  Red: #dc2626
                           ============================================= */

                        :root {
                            --sfa-navy: #1e3a5f;
                            --sfa-navy-deep: #162c46;
                            --sfa-red: #dc2626;
                            --sfa-red-dark: #b91c1c;
                        }

                        /* ---- LOGIN ---- */
                        .fi-simple-layout {
                            background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 50%, #1e3a5f 100%) !important;
                        }

                        /* ---- SIDEBAR — Dark Navy ---- */
                        .fi-sidebar {
                            background: linear-gradient(180deg, #1e3a5f 0%, #142638 100%) !important;
                            border-right: none !important;
                        }

                        .fi-sidebar-header {
                            border-bottom: 1px solid rgba(255,255,255,0.08) !important;
                            background: transparent !important;
                        }

                        /* Brand text in sidebar */
                        .fi-sidebar-header a span,
                        .fi-sidebar-header a {
                            color: #ffffff !important;
                        }

                        .fi-sidebar-nav {
                            padding: 8px 0 !important;
                        }

                        /* Nav items */
                        .fi-sidebar-item {
                            background: transparent !important;
                        }

                        .fi-sidebar-item-button {
                            background: transparent !important;
                            border-radius: 8px !important;
                            margin: 1px 10px !important;
                            padding: 8px 12px !important;
                            transition: all 0.15s ease !important;
                        }

                        .fi-sidebar-item-label {
                            color: rgba(255,255,255,0.75) !important;
                            font-weight: 500 !important;
                            font-size: 0.835rem !important;
                        }

                        .fi-sidebar-item-icon {
                            color: rgba(255,255,255,0.5) !important;
                            width: 18px !important;
                            height: 18px !important;
                        }

                        /* Hover */
                        .fi-sidebar-item-button:hover {
                            background: rgba(255,255,255,0.08) !important;
                        }
                        .fi-sidebar-item-button:hover .fi-sidebar-item-label {
                            color: #ffffff !important;
                        }
                        .fi-sidebar-item-button:hover .fi-sidebar-item-icon {
                            color: rgba(255,255,255,0.85) !important;
                        }

                        /* Active item — Red accent */
                        .fi-sidebar-item-active .fi-sidebar-item-button {
                            background: var(--sfa-red) !important;
                            box-shadow: 0 4px 14px rgba(220,38,38,0.35) !important;
                        }
                        .fi-sidebar-item-active .fi-sidebar-item-label {
                            color: #ffffff !important;
                            font-weight: 600 !important;
                        }
                        .fi-sidebar-item-active .fi-sidebar-item-icon {
                            color: #ffffff !important;
                        }

                        /* Group labels */
                        .fi-sidebar-group-label {
                            color: rgba(255,255,255,0.35) !important;
                            font-size: 0.65rem !important;
                            font-weight: 700 !important;
                            text-transform: uppercase !important;
                            letter-spacing: 0.08em !important;
                            padding-left: 22px !important;
                            padding-right: 12px !important;
                        }

                        /* Collapse buttons */
                        .fi-sidebar-close-btn,
                        .fi-sidebar-open-btn {
                            color: rgba(255,255,255,0.6) !important;
                        }
                        .fi-sidebar-close-btn:hover,
                        .fi-sidebar-open-btn:hover {
                            color: #ffffff !important;
                        }

                        /* Sidebar footer / user */
                        .fi-sidebar-footer {
                            border-top: 1px solid rgba(255,255,255,0.08) !important;
                        }

                        /* Sidebar scrollbar */
                        .fi-sidebar::-webkit-scrollbar { width: 4px; }
                        .fi-sidebar::-webkit-scrollbar-track { background: transparent; }
                        .fi-sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 2px; }

                        /* ---- TOPBAR ---- */
                        .fi-topbar {
                            background: #ffffff !important;
                            border-bottom: 1px solid #e5e7eb !important;
                            box-shadow: 0 1px 2px rgba(0,0,0,0.04) !important;
                        }
                        .fi-topbar nav { background: transparent !important; }
                        .dark .fi-topbar {
                            background: #111827 !important;
                            border-bottom-color: #1f2937 !important;
                        }

                        /* ---- BODY ---- */
                        .fi-body { background: #f8fafc !important; }
                        .fi-main { background: #f8fafc !important; }
                        .dark .fi-body { background: #0f172a !important; }
                        .dark .fi-main { background: #0f172a !important; }

                        /* ---- DARK MODE SIDEBAR ---- */
                        .dark .fi-sidebar {
                            background: linear-gradient(180deg, #0f172a 0%, #0a1120 100%) !important;
                        }
                        .dark .fi-sidebar-header {
                            border-bottom-color: rgba(255,255,255,0.06) !important;
                        }
                        .dark .fi-sidebar-item-active .fi-sidebar-item-button {
                            background: var(--sfa-red) !important;
                        }
                    </style>
                ')
            );
    }
}
