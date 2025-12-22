{{-- Enhanced Modern Admin Dashboard --}}
<x-filament-panels::page>
    @php
        $viewData = $this->getViewData();
        $compactStats = $viewData['compactStats'] ?? [];
        $quickActions = $viewData['quickActions'] ?? [];
        $upcomingEvents = $viewData['upcomingEvents'] ?? [];
        $chartData = $viewData['chartData'] ?? [];
        $attendanceStats = $viewData['attendanceStats'] ?? [];
        $financialSummary = $viewData['financialSummary'] ?? [];
        $pendingTasks = $viewData['pendingTasks'] ?? [];

        $hour = now()->hour;
        $greeting = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');
        $userName = auth()->user()->name ?? 'Admin';
    @endphp

    <div class="space-y-6">
        {{-- Welcome Header - St. Francis of Assisi School Colors --}}
        <div class="relative overflow-hidden rounded-2xl p-6 text-white shadow-xl" style="background: linear-gradient(135deg, #1e3a5f 0%, #2c4a6e 50%, #1e3a5f 100%);">
            <div class="absolute inset-0 bg-grid-white/10 [mask-image:linear-gradient(0deg,transparent,rgba(255,255,255,0.5))]"></div>
            {{-- Red accent stripe --}}
            <div class="absolute top-0 left-0 right-0 h-1" style="background: linear-gradient(90deg, #dc2626, #b91c1c);"></div>
            <div class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-4">
                    {{-- School Logo --}}
                    @php
                        $settings = \App\Models\SchoolSettings::first();
                        $logoUrl = $settings && $settings->school_logo ? asset('storage/' . $settings->school_logo) : null;
                    @endphp
                    @if($logoUrl)
                        <div class="hidden sm:block w-16 h-16 rounded-xl bg-white/10 p-1 backdrop-blur-sm">
                            <img src="{{ $logoUrl }}" alt="School Logo" class="w-full h-full object-contain">
                        </div>
                    @endif
                    <div>
                        <h1 class="text-2xl font-bold">{{ $greeting }}, {{ explode(' ', $userName)[0] }}!</h1>
                        <p class="mt-1 text-blue-200 text-sm">{{ now()->format('l, F j, Y') }} | School Management Dashboard</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="text-right hidden sm:block">
                        <div class="text-3xl font-bold">{{ now()->format('H:i') }}</div>
                        <div class="text-xs uppercase tracking-wide" style="color: #fca5a5;">{{ $this->getAcademicYearTermDisplay() }}</div>
                    </div>
                    {{-- Motto badge --}}
                    <div class="hidden md:flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 backdrop-blur-sm border border-white/20">
                        <span class="text-xs font-medium text-blue-100">For God and For Country</span>
                    </div>
                </div>
            </div>
            {{-- Decorative elements --}}
            <div class="absolute -top-10 -right-10 w-40 h-40 rounded-full blur-2xl" style="background: rgba(220, 38, 38, 0.15);"></div>
            <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
        </div>

        {{-- Quick Actions Row --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            @foreach(array_slice($quickActions, 0, 6) as $action)
                <a href="{{ $action['url'] }}" class="flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-all group">
                    <div class="w-9 h-9 rounded-lg bg-{{ $action['color'] }}-100 dark:bg-{{ $action['color'] }}-900/30 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <x-dynamic-component :component="$action['icon']" class="w-5 h-5 text-{{ $action['color'] }}-600 dark:text-{{ $action['color'] }}-400" />
                    </div>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $action['title'] }}</span>
                </a>
            @endforeach
        </div>

        {{-- Stats Cards Row - School Colors Theme (6 cards) --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            @php
                // St. Francis colors: Navy #1e3a5f, Red #dc2626
                $statStyles = [
                    'students' => ['gradient' => 'linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%)', 'accent' => '#1e3a5f'],
                    'teachers' => ['gradient' => 'linear-gradient(135deg, #2c4a6e 0%, #1e3a5f 100%)', 'accent' => '#2c4a6e'],
                    'fees' => ['gradient' => 'linear-gradient(135deg, #059669 0%, #047857 100%)', 'accent' => '#059669'],
                    'attendance' => ['gradient' => 'linear-gradient(135deg, #dc2626 0%, #b91c1c 100%)', 'accent' => '#dc2626'],
                    'homework' => ['gradient' => 'linear-gradient(135deg, #d97706 0%, #b45309 100%)', 'accent' => '#d97706'],
                    'events' => ['gradient' => 'linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%)', 'accent' => '#7c3aed'],
                ];
            @endphp
            @foreach($compactStats as $key => $stat)
                @php $style = $statStyles[$key] ?? ['gradient' => 'linear-gradient(135deg, #6b7280 0%, #4b5563 100%)', 'accent' => '#6b7280']; @endphp
                <div class="relative overflow-hidden rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-lg transition-all duration-300 group">
                    <div class="absolute top-0 right-0 w-24 h-24 rounded-full -mr-12 -mt-12 opacity-10 group-hover:opacity-20 transition-opacity duration-500" style="background: {{ $style['accent'] }};"></div>
                    <div class="p-4">
                        <div class="flex items-start justify-between">
                            <div class="w-11 h-11 rounded-xl flex items-center justify-center shadow-lg" style="background: {{ $style['gradient'] }}; box-shadow: 0 4px 14px {{ $style['accent'] }}40;">
                                <x-dynamic-component :component="'heroicon-o-' . $stat['icon']" class="w-5 h-5 text-white" />
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stat['value'] }}</div>
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mt-0.5">{{ $stat['label'] }}</div>
                        </div>
                        @if(!empty($stat['subtitle']))
                            <div class="mt-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                                <span class="text-[10px] text-gray-400 dark:text-gray-500">{{ $stat['subtitle'] }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pending Tasks Alert --}}
        @if(($pendingTasks['total'] ?? 0) > 0)
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                {{-- Header --}}
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center shadow-lg shadow-amber-500/25">
                                <x-heroicon-o-bell-alert class="w-5 h-5 text-white" />
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Items Requiring Attention</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $pendingTasks['total'] }} pending {{ Str::plural('task', $pendingTasks['total']) }}</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-center w-10 h-10 rounded-full bg-amber-100 dark:bg-amber-900/30">
                            <span class="text-lg font-bold text-amber-600 dark:text-amber-400">{{ $pendingTasks['total'] }}</span>
                        </div>
                    </div>
                </div>
                {{-- Task Items --}}
                <div class="p-5">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        {{-- Ungraded Submissions --}}
                        <div class="flex items-center gap-4 p-4 rounded-xl border" style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-color: #f59e0b;">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); box-shadow: 0 10px 15px -3px rgba(245, 158, 11, 0.3);">
                                <x-heroicon-o-document-check class="w-6 h-6 text-white" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-2xl font-bold" style="color: #b45309;">{{ $pendingTasks['ungraded'] ?? 0 }}</div>
                                <div class="text-sm font-medium" style="color: #d97706;">Ungraded Submissions</div>
                            </div>
                        </div>
                        {{-- Overdue Homework --}}
                        <div class="flex items-center gap-4 p-4 rounded-xl border" style="background: linear-gradient(135deg, #ffedd5 0%, #fed7aa 100%); border-color: #ea580c;">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%); box-shadow: 0 10px 15px -3px rgba(234, 88, 12, 0.3);">
                                <x-heroicon-o-clock class="w-6 h-6 text-white" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-2xl font-bold" style="color: #c2410c;">{{ $pendingTasks['overdueHomework'] ?? 0 }}</div>
                                <div class="text-sm font-medium" style="color: #ea580c;">Overdue Homework</div>
                            </div>
                        </div>
                        {{-- Overdue Fees --}}
                        <div class="flex items-center gap-4 p-4 rounded-xl border" style="background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); border-color: #dc2626;">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); box-shadow: 0 10px 15px -3px rgba(220, 38, 38, 0.3);">
                                <x-heroicon-o-banknotes class="w-6 h-6 text-white" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-2xl font-bold" style="color: #b91c1c;">{{ $pendingTasks['overdueBalances'] ?? 0 }}</div>
                                <div class="text-sm font-medium" style="color: #dc2626;">Overdue Fees</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Charts Section - 2x2 Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Enrollment by Grade --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                        Enrollment by Grade
                    </h3>
                </div>
                <div class="p-4">
                    <div id="enrollment-donut-chart" class="h-56"></div>
                </div>
            </div>

            {{-- Fee Collection Trend --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        Fee Collection Trend
                    </h3>
                </div>
                <div class="p-4">
                    <div id="fee-area-chart" class="h-56"></div>
                </div>
            </div>

            {{-- Today's Attendance --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-cyan-500"></span>
                        Today's Attendance
                    </h3>
                </div>
                <div class="p-4">
                    <div class="flex items-center justify-center mb-3">
                        <div id="attendance-radial-chart" class="h-32 w-32"></div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-green-50 dark:bg-green-900/20">
                            <div class="w-8 h-8 rounded-lg bg-green-100 dark:bg-green-800/50 flex items-center justify-center">
                                <x-heroicon-o-check class="w-4 h-4 text-green-600 dark:text-green-400" />
                            </div>
                            <div>
                                <div class="text-lg font-bold text-green-700 dark:text-green-300">{{ $attendanceStats['present'] ?? 0 }}</div>
                                <div class="text-[10px] text-green-600 dark:text-green-400">Present</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-red-50 dark:bg-red-900/20">
                            <div class="w-8 h-8 rounded-lg bg-red-100 dark:bg-red-800/50 flex items-center justify-center">
                                <x-heroicon-o-x-mark class="w-4 h-4 text-red-600 dark:text-red-400" />
                            </div>
                            <div>
                                <div class="text-lg font-bold text-red-700 dark:text-red-300">{{ $attendanceStats['absent'] ?? 0 }}</div>
                                <div class="text-[10px] text-red-600 dark:text-red-400">Absent</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-amber-50 dark:bg-amber-900/20">
                            <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-800/50 flex items-center justify-center">
                                <x-heroicon-o-clock class="w-4 h-4 text-amber-600 dark:text-amber-400" />
                            </div>
                            <div>
                                <div class="text-lg font-bold text-amber-700 dark:text-amber-300">{{ $attendanceStats['late'] ?? 0 }}</div>
                                <div class="text-[10px] text-amber-600 dark:text-amber-400">Late</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-blue-50 dark:bg-blue-900/20">
                            <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-800/50 flex items-center justify-center">
                                <x-heroicon-o-document-check class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div>
                                <div class="text-lg font-bold text-blue-700 dark:text-blue-300">{{ $attendanceStats['excused'] ?? 0 }}</div>
                                <div class="text-[10px] text-blue-600 dark:text-blue-400">Excused</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Subject Performance --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-violet-500"></span>
                        Top Subjects
                    </h3>
                </div>
                <div class="p-4">
                    <div id="subject-bar-chart" class="h-56"></div>
                </div>
            </div>
        </div>

        {{-- Upcoming Events --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gradient-to-r from-rose-50 to-transparent dark:from-rose-900/10">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-3">
                    <x-heroicon-o-calendar-days class="w-5 h-5 text-rose-500" />
                    Upcoming Events
                </h3>
                @if(Route::has('filament.admin.resources.events.index'))
                    <a href="{{ route('filament.admin.resources.events.index') }}" class="text-xs text-rose-600 hover:text-rose-700 font-medium px-2">View all</a>
                @endif
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 p-5">
                @forelse(array_slice($upcomingEvents, 0, 4) as $event)
                    <div class="flex items-start gap-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <div class="flex-shrink-0 w-12 text-center bg-gradient-to-br from-rose-500 to-pink-600 rounded-xl py-2 shadow-lg shadow-rose-500/20">
                            <div class="text-lg font-bold text-white">{{ \Carbon\Carbon::parse($event['start_date'])->format('d') }}</div>
                            <div class="text-[10px] text-rose-100 uppercase font-semibold">{{ \Carbon\Carbon::parse($event['start_date'])->format('M') }}</div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $event['title'] }}</p>
                            @if(!empty($event['location']))
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 flex items-center gap-1">
                                    <x-heroicon-o-map-pin class="w-3 h-3 text-gray-400" />
                                    {{ $event['location'] }}
                                </p>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-8">
                        <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-3">
                            <x-heroicon-o-calendar class="w-6 h-6 text-gray-400" />
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">No upcoming events</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background-color: rgba(156, 163, 175, 0.4); border-radius: 3px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background-color: rgba(156, 163, 175, 0.6); }
        .bg-grid-white\/10 { background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32' width='32' height='32' fill='none' stroke='rgb(255 255 255 / 0.1)'%3e%3cpath d='M0 .5H31.5V32'/%3e%3c/svg%3e"); }
    </style>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        let chartInstances = {};

        function initDashboardCharts() {
            const isDark = document.documentElement.classList.contains('dark');
            const textColor = isDark ? '#9CA3AF' : '#6B7280';
            const gridColor = isDark ? '#374151' : '#E5E7EB';
            const bgColor = isDark ? '#1F2937' : '#ffffff';

            Object.values(chartInstances).forEach(chart => {
                if (chart && typeof chart.destroy === 'function') chart.destroy();
            });
            chartInstances = {};

            // St. Francis School Colors: Navy #1e3a5f, Red #dc2626
            const schoolNavy = '#1e3a5f';
            const schoolRed = '#dc2626';

            // Enrollment Donut - School themed colors
            const enrollmentData = @json($chartData['gradeData'] ?? []);
            const enrollmentEl = document.querySelector("#enrollment-donut-chart");
            if (enrollmentData.length > 0 && enrollmentEl) {
                chartInstances.enrollment = new ApexCharts(enrollmentEl, {
                    series: enrollmentData.map(item => item.count),
                    labels: enrollmentData.map(item => item.grade),
                    chart: { type: 'donut', height: 220, background: 'transparent' },
                    colors: ['#1e3a5f', '#dc2626', '#2c5282', '#b91c1c', '#3b6998', '#059669', '#d97706', '#7c3aed'],
                    legend: { position: 'bottom', fontSize: '11px', labels: { colors: textColor }, itemMargin: { horizontal: 6, vertical: 4 } },
                    dataLabels: { enabled: false },
                    stroke: { width: 3, colors: [bgColor] },
                    plotOptions: { pie: { donut: { size: '70%', labels: { show: true, total: { show: true, label: 'Total', color: textColor, fontSize: '12px' }, value: { fontSize: '22px', fontWeight: 700, color: isDark ? '#F9FAFB' : '#111827' } } } } }
                });
                chartInstances.enrollment.render();
            }

            // Fee Area Chart - School Navy
            const monthlyData = @json($financialSummary['monthly'] ?? []);
            const feeEl = document.querySelector("#fee-area-chart");
            if (monthlyData.length > 0 && feeEl) {
                chartInstances.fees = new ApexCharts(feeEl, {
                    series: [{ name: 'Collected', data: monthlyData.map(item => item.collected) }],
                    chart: { type: 'area', height: 220, background: 'transparent', toolbar: { show: false } },
                    stroke: { curve: 'smooth', width: 3 },
                    fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.6, opacityTo: 0.1, stops: [0, 90, 100] } },
                    colors: ['#059669'],
                    xaxis: { categories: monthlyData.map(item => item.month), labels: { style: { colors: textColor, fontSize: '11px' } }, axisBorder: { show: false }, axisTicks: { show: false } },
                    yaxis: { labels: { style: { colors: textColor, fontSize: '11px' }, formatter: (val) => val > 0 ? 'K' + (val/1000).toFixed(0) + 'k' : '0' } },
                    grid: { borderColor: gridColor, strokeDashArray: 4 },
                    tooltip: { y: { formatter: (val) => 'K' + val.toLocaleString() } }
                });
                chartInstances.fees.render();
            }

            // Attendance Radial - School Red for low, Navy for good
            const attendanceRate = @json($attendanceStats['rate'] ?? 0);
            const attendanceEl = document.querySelector("#attendance-radial-chart");
            if (attendanceEl) {
                const rateColor = attendanceRate >= 80 ? '#1e3a5f' : (attendanceRate >= 60 ? '#d97706' : '#dc2626');
                chartInstances.attendance = new ApexCharts(attendanceEl, {
                    series: [attendanceRate],
                    chart: { type: 'radialBar', height: 140, background: 'transparent', sparkline: { enabled: true } },
                    colors: [rateColor],
                    plotOptions: { radialBar: { hollow: { size: '60%' }, track: { background: isDark ? '#374151' : '#E5E7EB', strokeWidth: '100%' }, dataLabels: { show: true, name: { show: false }, value: { show: true, fontSize: '26px', fontWeight: 700, color: isDark ? '#F9FAFB' : '#111827', formatter: (val) => val + '%' } } } }
                });
                chartInstances.attendance.render();
            }

            // Subject Bar - School themed colors
            const resultData = @json($chartData['resultData'] ?? []);
            const subjectEl = document.querySelector("#subject-bar-chart");
            if (resultData.length > 0 && subjectEl) {
                chartInstances.subjects = new ApexCharts(subjectEl, {
                    series: [{ name: 'Average', data: resultData.map(item => parseFloat(item.average).toFixed(1)) }],
                    chart: { type: 'bar', height: 220, background: 'transparent', toolbar: { show: false } },
                    plotOptions: { bar: { horizontal: true, borderRadius: 6, barHeight: '70%', distributed: true } },
                    colors: ['#1e3a5f', '#dc2626', '#2c5282', '#059669', '#d97706'],
                    xaxis: { categories: resultData.map(item => item.name.length > 10 ? item.name.substring(0, 10) + '...' : item.name), labels: { style: { colors: textColor, fontSize: '11px' }, formatter: (val) => val + '%' }, axisBorder: { show: false }, axisTicks: { show: false } },
                    yaxis: { labels: { style: { colors: textColor, fontSize: '11px' } } },
                    grid: { borderColor: gridColor, strokeDashArray: 4, xaxis: { lines: { show: true } }, yaxis: { lines: { show: false } } },
                    dataLabels: { enabled: true, formatter: (val) => val + '%', style: { fontSize: '11px', fontWeight: 600 }, offsetX: -5 },
                    legend: { show: false },
                    tooltip: { enabled: false }
                });
                chartInstances.subjects.render();
            }
        }

        document.addEventListener('DOMContentLoaded', initDashboardCharts);
        new MutationObserver(mutations => mutations.forEach(m => m.attributeName === 'class' && setTimeout(initDashboardCharts, 100))).observe(document.documentElement, { attributes: true });
    </script>
    @endpush
</x-filament-panels::page>
