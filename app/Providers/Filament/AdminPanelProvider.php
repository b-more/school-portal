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
                        /* St. Francis of Assisi School - Corporate Colors */
                        /* Navy Blue: #1e3a5f | Crimson Red: #dc2626 */

                        :root {
                            --school-navy: 30, 58, 95;
                            --school-red: 220, 38, 38;
                        }

                        /* Login Page Styling */
                        .fi-simple-layout {
                            background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 50%, #1e3a5f 100%) !important;
                        }

                        /* ===== SIDEBAR - Navy Blue Background ===== */
                        .fi-sidebar {
                            background: linear-gradient(180deg, #1e3a5f 0%, #162c46 100%) !important;
                        }

                        .fi-sidebar-header {
                            border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
                        }

                        /* Sidebar Nav Items - White Text */
                        .fi-sidebar-item-label {
                            color: rgba(255, 255, 255, 0.85) !important;
                            font-weight: 500;
                        }

                        .fi-sidebar-item-icon {
                            color: rgba(255, 255, 255, 0.7) !important;
                        }

                        /* Sidebar Hover */
                        .fi-sidebar-item-button:hover {
                            background: rgba(255, 255, 255, 0.1) !important;
                            border-radius: 0.5rem;
                        }

                        .fi-sidebar-item-button:hover .fi-sidebar-item-label,
                        .fi-sidebar-item-button:hover .fi-sidebar-item-icon {
                            color: white !important;
                        }

                        /* ===== ACTIVE MENU - Red Background ===== */
                        .fi-sidebar-item-active .fi-sidebar-item-button {
                            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%) !important;
                            border-radius: 0.5rem;
                            margin: 0 0.5rem;
                            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.4);
                        }

                        .fi-sidebar-item-active .fi-sidebar-item-label {
                            color: white !important;
                            font-weight: 600;
                        }

                        .fi-sidebar-item-active .fi-sidebar-item-icon {
                            color: white !important;
                        }

                        /* Sidebar Group Labels */
                        .fi-sidebar-group-label {
                            color: rgba(255, 255, 255, 0.5) !important;
                            font-size: 0.7rem;
                            text-transform: uppercase;
                            letter-spacing: 0.05em;
                        }

                        /* Sidebar Collapse Button */
                        .fi-sidebar-close-btn,
                        .fi-sidebar-open-btn {
                            color: white !important;
                        }

                        /* Topbar */
                        .fi-topbar {
                            background: white !important;
                            border-bottom: 1px solid #e5e7eb;
                        }

                        .dark .fi-topbar {
                            background: #1f2937 !important;
                            border-bottom: 1px solid #374151;
                        }

                        /* Primary Buttons */
                        .fi-btn-primary {
                            background: linear-gradient(135deg, #1e3a5f, #2c5282) !important;
                            box-shadow: 0 4px 12px rgba(30, 58, 95, 0.25);
                        }

                        .fi-btn-primary:hover {
                            background: linear-gradient(135deg, #162c46, #1e3a5f) !important;
                        }

                        /* Table Headers */
                        .fi-ta-header-cell {
                            background: rgba(30, 58, 95, 0.05) !important;
                        }

                        .dark .fi-ta-header-cell {
                            background: rgba(30, 58, 95, 0.2) !important;
                        }

                        /* Section Headers */
                        .fi-header-heading {
                            color: #1e3a5f !important;
                        }

                        .dark .fi-header-heading {
                            color: #93c5fd !important;
                        }

                        /* Pagination Active */
                        .fi-pagination-item-active {
                            background: #1e3a5f !important;
                        }

                        /* Tabs */
                        .fi-tabs-tab-active {
                            border-color: #1e3a5f !important;
                            color: #1e3a5f !important;
                        }

                        .dark .fi-tabs-tab-active {
                            border-color: #93c5fd !important;
                            color: #93c5fd !important;
                        }

                        /* Toggle */
                        .fi-toggle-input:checked {
                            background-color: #1e3a5f !important;
                        }

                        /* Custom Scrollbar */
                        ::-webkit-scrollbar {
                            width: 8px;
                            height: 8px;
                        }
                        ::-webkit-scrollbar-track {
                            background: #f1f5f9;
                        }
                        ::-webkit-scrollbar-thumb {
                            background: #cbd5e1;
                            border-radius: 4px;
                        }
                        ::-webkit-scrollbar-thumb:hover {
                            background: #94a3b8;
                        }

                        /* Sidebar Scrollbar */
                        .fi-sidebar::-webkit-scrollbar-track {
                            background: rgba(255, 255, 255, 0.05);
                        }
                        .fi-sidebar::-webkit-scrollbar-thumb {
                            background: rgba(255, 255, 255, 0.2);
                        }
                        .fi-sidebar::-webkit-scrollbar-thumb:hover {
                            background: rgba(255, 255, 255, 0.3);
                        }

                        .dark ::-webkit-scrollbar-track {
                            background: #1e293b;
                        }
                        .dark ::-webkit-scrollbar-thumb {
                            background: #475569;
                        }
                    </style>
                ')
            );
    }
}
