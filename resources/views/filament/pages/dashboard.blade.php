{{-- St. Francis of Assisi — Corporate Admin Dashboard --}}
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
        $topPerformers = $viewData['topPerformers'] ?? [];
        $recentPayments = $viewData['recentPayments'] ?? [];
        $pendingSubmissions = $viewData['pendingSubmissions'] ?? [];
        $overdueFees = $viewData['overdueFeees'] ?? [];
        $genderStats = $viewData['genderStats'] ?? [];
        $gradeCapacity = $viewData['gradeCapacity'] ?? [];
        $monthlyComparison = $viewData['monthlyComparison'] ?? [];
        $attendanceRegister = $viewData['attendanceRegister'] ?? [];

        $hour = now()->hour;
        $greeting = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');
        $userName = auth()->user()->name ?? 'Admin';
        $firstName = explode(' ', $userName)[0];

        $settings = \App\Models\SchoolSettings::first();
        $logoUrl = $settings && $settings->school_logo ? asset('storage/' . $settings->school_logo) : null;
    @endphp

    <div class="sfa-dash space-y-5">

        {{-- ============================================================
             HEADER BAR — Compact command strip
             ============================================================ --}}
        <div class="sfa-header">
            <div class="sfa-header-inner">
                <div class="sfa-header-left">
                    @if($logoUrl)
                        <div class="sfa-header-logo">
                            <img src="{{ $logoUrl }}" alt="Logo">
                        </div>
                    @endif
                    <div>
                        <h1 class="sfa-header-title">{{ $greeting }}, {{ $firstName }}</h1>
                        <p class="sfa-header-sub">{{ now()->format('l, j F Y') }}</p>
                    </div>
                </div>
                <div class="sfa-header-right">
                    <div class="sfa-header-meta">
                        <span class="sfa-header-term">{{ $this->getAcademicYearTermDisplay() }}</span>
                        <span class="sfa-header-time">{{ now()->format('H:i') }}</span>
                    </div>
                    <div class="sfa-header-motto">For God and For Country</div>
                </div>
            </div>
            <div class="sfa-header-stripe"></div>
        </div>

        {{-- ============================================================
             QUICK ACTIONS — Compact pill row
             ============================================================ --}}
        @if(count($quickActions) > 0)
            <div class="sfa-actions-row">
                @foreach(array_slice($quickActions, 0, 7) as $action)
                    <a href="{{ $action['url'] }}" class="sfa-action-pill">
                        <x-dynamic-component :component="$action['icon']" class="sfa-action-icon" />
                        <span>{{ $action['title'] }}</span>
                    </a>
                @endforeach
            </div>
        @endif

        {{-- ============================================================
             KPI STRIP — Six metric cards
             ============================================================ --}}
        <div class="sfa-kpi-grid">
            @php
                $kpiAccents = [
                    'students'   => '#1e3a5f',
                    'teachers'   => '#334e75',
                    'fees'       => '#059669',
                    'attendance' => '#dc2626',
                    'homework'   => '#d97706',
                    'events'     => '#7c3aed',
                ];
            @endphp
            @foreach($compactStats as $key => $stat)
                <div class="sfa-kpi-card" style="--kpi-accent: {{ $kpiAccents[$key] ?? '#6b7280' }};">
                    <div class="sfa-kpi-top">
                        <div class="sfa-kpi-icon-wrap">
                            <x-dynamic-component :component="'heroicon-o-' . $stat['icon']" class="sfa-kpi-icon" />
                        </div>
                        <span class="sfa-kpi-label">{{ $stat['label'] }}</span>
                    </div>
                    <div class="sfa-kpi-value">{{ $stat['value'] }}</div>
                    @if(!empty($stat['subtitle']))
                        <div class="sfa-kpi-sub">{{ $stat['subtitle'] }}</div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- ============================================================
             ATTENTION BAR — Pending tasks (only if > 0)
             ============================================================ --}}
        @if(($pendingTasks['total'] ?? 0) > 0)
            <div class="sfa-attention-bar">
                <div class="sfa-attention-header">
                    <x-heroicon-s-exclamation-triangle class="sfa-attention-icon" />
                    <span class="sfa-attention-title">{{ $pendingTasks['total'] }} {{ Str::plural('item', $pendingTasks['total']) }} requiring attention</span>
                </div>
                <div class="sfa-attention-items">
                    @if(($pendingTasks['ungraded'] ?? 0) > 0)
                        <div class="sfa-attention-chip sfa-attention-chip--amber">
                            <span class="sfa-attention-count">{{ $pendingTasks['ungraded'] }}</span>
                            <span>Ungraded</span>
                        </div>
                    @endif
                    @if(($pendingTasks['overdueHomework'] ?? 0) > 0)
                        <div class="sfa-attention-chip sfa-attention-chip--orange">
                            <span class="sfa-attention-count">{{ $pendingTasks['overdueHomework'] }}</span>
                            <span>Overdue H/W</span>
                        </div>
                    @endif
                    @if(($pendingTasks['overdueBalances'] ?? 0) > 0)
                        <div class="sfa-attention-chip sfa-attention-chip--red">
                            <span class="sfa-attention-count">{{ $pendingTasks['overdueBalances'] }}</span>
                            <span>Overdue Fees</span>
                        </div>
                    @endif
                    @if(($pendingTasks['noFees'] ?? 0) > 0)
                        <div class="sfa-attention-chip sfa-attention-chip--gray">
                            <span class="sfa-attention-count">{{ $pendingTasks['noFees'] }}</span>
                            <span>No Fee Assigned</span>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- ============================================================
             CHARTS — 2×2 grid
             ============================================================ --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            {{-- Enrollment by Grade --}}
            <div class="sfa-card">
                <div class="sfa-card-head">
                    <div class="sfa-card-dot" style="background:#1e3a5f"></div>
                    <h3 class="sfa-card-title">Enrollment by Grade</h3>
                </div>
                <div class="sfa-card-body">
                    <div id="chart-enrollment" style="height:240px"></div>
                </div>
            </div>

            {{-- Fee Collection Trend --}}
            <div class="sfa-card">
                <div class="sfa-card-head">
                    <div class="sfa-card-dot" style="background:#059669"></div>
                    <h3 class="sfa-card-title">Fee Collection Trend</h3>
                    @if(($financialSummary['totalOutstanding'] ?? 0) > 0)
                        <span class="sfa-card-badge sfa-card-badge--red">K{{ number_format($financialSummary['totalOutstanding'], 0) }} outstanding</span>
                    @endif
                </div>
                <div class="sfa-card-body">
                    <div id="chart-fees" style="height:240px"></div>
                </div>
            </div>

            {{-- Subject Performance --}}
            <div class="sfa-card">
                <div class="sfa-card-head">
                    <div class="sfa-card-dot" style="background:#7c3aed"></div>
                    <h3 class="sfa-card-title">Subject Performance</h3>
                </div>
                <div class="sfa-card-body">
                    <div id="chart-subjects" style="height:240px"></div>
                </div>
            </div>
        </div>

        {{-- ============================================================
             TODAY'S ATTENDANCE REGISTER
             ============================================================ --}}
        @if(!empty($attendanceRegister['register']))
        <div class="sfa-card">
            <div class="sfa-card-head">
                <div class="sfa-card-dot" style="background:#dc2626"></div>
                <h3 class="sfa-card-title">Today's Attendance Register</h3>
                <span class="sfa-card-badge">{{ $attendanceRegister['date'] ?? '' }}</span>
                <span class="sfa-card-badge">{{ $attendanceRegister['grandTotal'] ?? 0 }} marked</span>
            </div>
            <div class="sfa-reg-scroll">
                <table class="sfa-reg">
                    <thead>
                        <tr>
                            <th class="sfa-reg-grade" rowspan="2">Grade</th>
                            <th class="sfa-reg-group" colspan="2">Present</th>
                            <th class="sfa-reg-group" colspan="2">Sick</th>
                            <th class="sfa-reg-group" colspan="2">Permission</th>
                            <th class="sfa-reg-group" colspan="2">Absent</th>
                            <th class="sfa-reg-group sfa-reg-group--total" colspan="3">Total</th>
                        </tr>
                        <tr>
                            <th class="sfa-reg-sub sfa-reg-sub--boy">B</th>
                            <th class="sfa-reg-sub sfa-reg-sub--girl">G</th>
                            <th class="sfa-reg-sub sfa-reg-sub--boy">B</th>
                            <th class="sfa-reg-sub sfa-reg-sub--girl">G</th>
                            <th class="sfa-reg-sub sfa-reg-sub--boy">B</th>
                            <th class="sfa-reg-sub sfa-reg-sub--girl">G</th>
                            <th class="sfa-reg-sub sfa-reg-sub--boy">B</th>
                            <th class="sfa-reg-sub sfa-reg-sub--girl">G</th>
                            <th class="sfa-reg-sub sfa-reg-sub--boy">B</th>
                            <th class="sfa-reg-sub sfa-reg-sub--girl">G</th>
                            <th class="sfa-reg-sub sfa-reg-sub--all">All</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attendanceRegister['register'] as $row)
                            <tr class="{{ $row['grand_total'] > 0 ? '' : 'sfa-reg-empty' }}">
                                <td class="sfa-reg-name">{{ $row['name'] }}</td>
                                <td class="sfa-reg-val">{{ $row['boys']['present'] ?: '-' }}</td>
                                <td class="sfa-reg-val">{{ $row['girls']['present'] ?: '-' }}</td>
                                <td class="sfa-reg-val">{{ $row['boys']['late'] ?: '-' }}</td>
                                <td class="sfa-reg-val">{{ $row['girls']['late'] ?: '-' }}</td>
                                <td class="sfa-reg-val">{{ $row['boys']['excused'] ?: '-' }}</td>
                                <td class="sfa-reg-val">{{ $row['girls']['excused'] ?: '-' }}</td>
                                <td class="sfa-reg-val">{{ $row['boys']['absent'] ?: '-' }}</td>
                                <td class="sfa-reg-val">{{ $row['girls']['absent'] ?: '-' }}</td>
                                <td class="sfa-reg-val sfa-reg-val--total">{{ $row['boys_total'] ?: '-' }}</td>
                                <td class="sfa-reg-val sfa-reg-val--total">{{ $row['girls_total'] ?: '-' }}</td>
                                <td class="sfa-reg-val sfa-reg-val--grand">{{ $row['grand_total'] ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="sfa-reg-footer">
                            <td class="sfa-reg-name">TOTAL</td>
                            <td class="sfa-reg-val">{{ $attendanceRegister['totals']['boys']['present'] ?? 0 }}</td>
                            <td class="sfa-reg-val">{{ $attendanceRegister['totals']['girls']['present'] ?? 0 }}</td>
                            <td class="sfa-reg-val">{{ $attendanceRegister['totals']['boys']['late'] ?? 0 }}</td>
                            <td class="sfa-reg-val">{{ $attendanceRegister['totals']['girls']['late'] ?? 0 }}</td>
                            <td class="sfa-reg-val">{{ $attendanceRegister['totals']['boys']['excused'] ?? 0 }}</td>
                            <td class="sfa-reg-val">{{ $attendanceRegister['totals']['girls']['excused'] ?? 0 }}</td>
                            <td class="sfa-reg-val">{{ $attendanceRegister['totals']['boys']['absent'] ?? 0 }}</td>
                            <td class="sfa-reg-val">{{ $attendanceRegister['totals']['girls']['absent'] ?? 0 }}</td>
                            <td class="sfa-reg-val sfa-reg-val--total">{{ $attendanceRegister['totalBoys'] ?? 0 }}</td>
                            <td class="sfa-reg-val sfa-reg-val--total">{{ $attendanceRegister['totalGirls'] ?? 0 }}</td>
                            <td class="sfa-reg-val sfa-reg-val--grand">{{ $attendanceRegister['grandTotal'] ?? 0 }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        @endif

        {{-- ============================================================
             MONTH-OVER-MONTH COMPARISON
             ============================================================ --}}
        <div class="sfa-card">
            <div class="sfa-card-head">
                <div class="sfa-card-dot" style="background:#7c3aed"></div>
                <h3 class="sfa-card-title">This Month vs Last Month</h3>
            </div>
            <div class="sfa-card-body">
                <div class="sfa-compare-grid">
                    {{-- Enrollments --}}
                    <div class="sfa-compare-item">
                        <div class="sfa-compare-icon" style="--ci-color: #1e3a5f">
                            <x-heroicon-o-user-plus class="w-5 h-5" />
                        </div>
                        <div class="sfa-compare-data">
                            <span class="sfa-compare-label">New Enrollments</span>
                            <div class="sfa-compare-values">
                                <span class="sfa-compare-current">{{ $monthlyComparison['enrollments']['current'] ?? 0 }}</span>
                                <span class="sfa-compare-vs">vs {{ $monthlyComparison['enrollments']['previous'] ?? 0 }}</span>
                                @php $enrollChange = $monthlyComparison['enrollments']['change'] ?? 0; @endphp
                                <span class="sfa-compare-change {{ $enrollChange >= 0 ? 'sfa-compare-change--up' : 'sfa-compare-change--down' }}">
                                    @if($enrollChange >= 0) +@endif{{ $enrollChange }}%
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Fee Collection --}}
                    <div class="sfa-compare-item">
                        <div class="sfa-compare-icon" style="--ci-color: #059669">
                            <x-heroicon-o-banknotes class="w-5 h-5" />
                        </div>
                        <div class="sfa-compare-data">
                            <span class="sfa-compare-label">Fees Collected</span>
                            <div class="sfa-compare-values">
                                <span class="sfa-compare-current">K{{ number_format($monthlyComparison['fees']['current'] ?? 0, 0) }}</span>
                                <span class="sfa-compare-vs">vs K{{ number_format($monthlyComparison['fees']['previous'] ?? 0, 0) }}</span>
                                @php $feeChange = $monthlyComparison['fees']['change'] ?? 0; @endphp
                                <span class="sfa-compare-change {{ $feeChange >= 0 ? 'sfa-compare-change--up' : 'sfa-compare-change--down' }}">
                                    @if($feeChange >= 0) +@endif{{ $feeChange }}%
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Attendance --}}
                    <div class="sfa-compare-item">
                        <div class="sfa-compare-icon" style="--ci-color: #dc2626">
                            <x-heroicon-o-clipboard-document-check class="w-5 h-5" />
                        </div>
                        <div class="sfa-compare-data">
                            <span class="sfa-compare-label">Attendance Rate</span>
                            <div class="sfa-compare-values">
                                <span class="sfa-compare-current">{{ $monthlyComparison['attendance']['current'] ?? 0 }}%</span>
                                <span class="sfa-compare-vs">vs {{ $monthlyComparison['attendance']['previous'] ?? 0 }}%</span>
                                @php
                                    $attDiff = ($monthlyComparison['attendance']['current'] ?? 0) - ($monthlyComparison['attendance']['previous'] ?? 0);
                                @endphp
                                <span class="sfa-compare-change {{ $attDiff >= 0 ? 'sfa-compare-change--up' : 'sfa-compare-change--down' }}">
                                    @if($attDiff >= 0) +@endif{{ $attDiff }}pts
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================
             GENDER + GRADE CAPACITY — Side by side
             ============================================================ --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            {{-- Gender Distribution --}}
            <div class="sfa-card">
                <div class="sfa-card-head">
                    <div class="sfa-card-dot" style="background:#7c3aed"></div>
                    <h3 class="sfa-card-title">Gender Distribution</h3>
                    <span class="sfa-card-badge">{{ $genderStats['total'] ?? 0 }} students</span>
                </div>
                <div class="sfa-card-body">
                    <div class="sfa-gender-layout">
                        <div id="chart-gender" style="height:200px"></div>
                        <div class="sfa-gender-legend">
                            <div class="sfa-gender-row">
                                <div class="sfa-gender-dot" style="background: #1e3a5f"></div>
                                <span class="sfa-gender-label">Male</span>
                                <span class="sfa-gender-value">{{ $genderStats['male'] ?? 0 }}</span>
                                <span class="sfa-gender-pct">{{ $genderStats['malePercent'] ?? 0 }}%</span>
                            </div>
                            <div class="sfa-gender-row">
                                <div class="sfa-gender-dot" style="background: #dc2626"></div>
                                <span class="sfa-gender-label">Female</span>
                                <span class="sfa-gender-value">{{ $genderStats['female'] ?? 0 }}</span>
                                <span class="sfa-gender-pct">{{ $genderStats['femalePercent'] ?? 0 }}%</span>
                            </div>
                            @if(($genderStats['other'] ?? 0) > 0)
                                <div class="sfa-gender-row">
                                    <div class="sfa-gender-dot" style="background: #6b7280"></div>
                                    <span class="sfa-gender-label">Other</span>
                                    <span class="sfa-gender-value">{{ $genderStats['other'] }}</span>
                                    <span class="sfa-gender-pct">{{ $genderStats['total'] > 0 ? round(($genderStats['other'] / $genderStats['total']) * 100) : 0 }}%</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Grade Capacity --}}
            <div class="sfa-card">
                <div class="sfa-card-head">
                    <div class="sfa-card-dot" style="background:#d97706"></div>
                    <h3 class="sfa-card-title">Grade Capacity Utilization</h3>
                </div>
                <div class="sfa-card-body sfa-capacity-body">
                    @forelse($gradeCapacity as $gc)
                        <div class="sfa-cap-row">
                            <div class="sfa-cap-info">
                                <span class="sfa-cap-name">{{ $gc['name'] }}</span>
                                <span class="sfa-cap-nums">{{ $gc['enrolled'] }}/{{ $gc['capacity'] ?: '—' }}</span>
                            </div>
                            <div class="sfa-cap-bar-wrap">
                                <div class="sfa-cap-bar {{ $gc['percent'] >= 90 ? 'sfa-cap-bar--danger' : ($gc['percent'] >= 70 ? 'sfa-cap-bar--warning' : 'sfa-cap-bar--ok') }}"
                                     style="width: {{ min($gc['percent'], 100) }}%">
                                </div>
                            </div>
                            <span class="sfa-cap-pct">{{ $gc['percent'] }}%</span>
                        </div>
                    @empty
                        <div class="sfa-list-empty">
                            <x-heroicon-o-academic-cap class="sfa-list-empty-icon" />
                            <span>No grade data available</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ============================================================
             TOP PERFORMERS TABLE
             ============================================================ --}}
        @if(count($topPerformers) > 0)
            <div class="sfa-card">
                <div class="sfa-card-head">
                    <div class="sfa-card-dot" style="background:#1e3a5f"></div>
                    <h3 class="sfa-card-title">Top Performers — Current Term</h3>
                </div>
                <div class="sfa-table-wrap">
                    <table class="sfa-table">
                        <thead>
                            <tr>
                                <th style="width:60px">#</th>
                                <th>Student</th>
                                <th>Student ID</th>
                                <th class="text-center">Subjects</th>
                                <th class="text-right">Average</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topPerformers as $p)
                                <tr>
                                    <td>
                                        @if($p['rank'] <= 3)
                                            <span class="sfa-rank sfa-rank--{{ $p['rank'] }}">{{ $p['rank'] }}</span>
                                        @else
                                            <span class="sfa-rank">{{ $p['rank'] }}</span>
                                        @endif
                                    </td>
                                    <td class="sfa-table-name">{{ $p['name'] }}</td>
                                    <td class="sfa-table-mono">{{ $p['student_id'] }}</td>
                                    <td class="text-center">{{ $p['subjects'] }}</td>
                                    <td class="text-right">
                                        <span class="sfa-avg-badge {{ $p['average'] >= 75 ? 'sfa-avg--high' : ($p['average'] >= 50 ? 'sfa-avg--mid' : 'sfa-avg--low') }}">
                                            {{ $p['average'] }}%
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- ============================================================
             DATA PANELS — 3-column: Payments, Submissions, Balances
             ============================================================ --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            {{-- Recent Payments --}}
            <div class="sfa-card sfa-card--mini">
                <div class="sfa-card-head">
                    <div class="sfa-card-dot" style="background:#059669"></div>
                    <h3 class="sfa-card-title">Recent Payments</h3>
                </div>
                <div class="sfa-list">
                    @forelse($recentPayments as $payment)
                        <div class="sfa-list-item">
                            <div class="sfa-list-main">
                                <span class="sfa-list-name">{{ $payment['student'] }}</span>
                                <span class="sfa-list-meta">{{ $payment['grade'] }} &middot; {{ $payment['date'] }}</span>
                            </div>
                            <div class="sfa-list-end">
                                <span class="sfa-list-amount sfa-list-amount--green">K{{ number_format($payment['amount'], 0) }}</span>
                                <span class="sfa-list-status sfa-list-status--{{ $payment['status'] }}">{{ ucfirst($payment['status']) }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="sfa-list-empty">
                            <x-heroicon-o-banknotes class="sfa-list-empty-icon" />
                            <span>No recent payments</span>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Pending Submissions --}}
            <div class="sfa-card sfa-card--mini">
                <div class="sfa-card-head">
                    <div class="sfa-card-dot" style="background:#d97706"></div>
                    <h3 class="sfa-card-title">Ungraded Work</h3>
                </div>
                <div class="sfa-list">
                    @forelse($pendingSubmissions as $sub)
                        <div class="sfa-list-item">
                            <div class="sfa-list-main">
                                <span class="sfa-list-name">{{ $sub['student'] }}</span>
                                <span class="sfa-list-meta">{{ $sub['subject'] }} &middot; {{ $sub['homework'] }}</span>
                            </div>
                            <div class="sfa-list-end">
                                <span class="sfa-list-time">{{ $sub['submitted'] }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="sfa-list-empty">
                            <x-heroicon-o-check-circle class="sfa-list-empty-icon" />
                            <span>All graded</span>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Outstanding Balances --}}
            <div class="sfa-card sfa-card--mini">
                <div class="sfa-card-head">
                    <div class="sfa-card-dot" style="background:#dc2626"></div>
                    <h3 class="sfa-card-title">Outstanding Balances</h3>
                </div>
                <div class="sfa-list">
                    @forelse($overdueFees as $fee)
                        <div class="sfa-list-item">
                            <div class="sfa-list-main">
                                <span class="sfa-list-name">{{ $fee['student'] }}</span>
                                <span class="sfa-list-meta">{{ $fee['grade'] }} &middot; {{ $fee['days'] }}d overdue</span>
                            </div>
                            <div class="sfa-list-end">
                                <span class="sfa-list-amount sfa-list-amount--{{ $fee['status'] }}">K{{ number_format($fee['balance'], 0) }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="sfa-list-empty">
                            <x-heroicon-o-check-circle class="sfa-list-empty-icon" />
                            <span>No outstanding balances</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ============================================================
             UPCOMING EVENTS
             ============================================================ --}}
        <div class="sfa-card">
            <div class="sfa-card-head">
                <div class="sfa-card-dot" style="background:#dc2626"></div>
                <h3 class="sfa-card-title">Upcoming Events</h3>
                @if(Route::has('filament.admin.resources.events.index'))
                    <a href="{{ route('filament.admin.resources.events.index') }}" class="sfa-card-link">View all</a>
                @endif
            </div>
            <div class="sfa-events-grid">
                @forelse(array_slice($upcomingEvents, 0, 5) as $event)
                    <div class="sfa-event-card">
                        <div class="sfa-event-date">
                            <span class="sfa-event-day">{{ \Carbon\Carbon::parse($event['start_date'])->format('d') }}</span>
                            <span class="sfa-event-month">{{ \Carbon\Carbon::parse($event['start_date'])->format('M') }}</span>
                        </div>
                        <div class="sfa-event-info">
                            <span class="sfa-event-title">{{ $event['title'] }}</span>
                            @if(!empty($event['location']))
                                <span class="sfa-event-loc">{{ $event['location'] }}</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="sfa-list-empty" style="grid-column: 1 / -1;">
                        <x-heroicon-o-calendar class="sfa-list-empty-icon" />
                        <span>No upcoming events scheduled</span>
                    </div>
                @endforelse
            </div>
        </div>

    </div>

    {{-- ============================================================
         STYLES — Scoped under .sfa-dash
         ============================================================ --}}
    <style>
        /* ---- Load DM Sans for the dashboard ---- */
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&display=swap');

        .sfa-dash {
            font-family: 'DM Sans', ui-sans-serif, system-ui, sans-serif;
            --navy: #1e3a5f;
            --navy-light: #2c5282;
            --red: #dc2626;
            --red-dark: #b91c1c;
            --green: #059669;
            --amber: #d97706;
            --card-bg: #ffffff;
            --card-border: #e5e7eb;
            --text-primary: #111827;
            --text-secondary: #6b7280;
            --text-tertiary: #9ca3af;
            --surface: #f8fafc;
        }

        .dark .sfa-dash {
            --card-bg: #1f2937;
            --card-border: #374151;
            --text-primary: #f9fafb;
            --text-secondary: #9ca3af;
            --text-tertiary: #6b7280;
            --surface: #111827;
        }

        /* ---- HEADER ---- */
        .sfa-header {
            position: relative;
            background: var(--navy);
            border-radius: 12px;
            overflow: hidden;
        }
        .sfa-header-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 24px;
            gap: 16px;
            flex-wrap: wrap;
        }
        .sfa-header-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .sfa-header-logo {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            background: rgba(255,255,255,0.12);
            padding: 4px;
            flex-shrink: 0;
        }
        .sfa-header-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .sfa-header-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #ffffff;
            line-height: 1.3;
            margin: 0;
        }
        .sfa-header-sub {
            font-size: 0.8rem;
            color: rgba(255,255,255,0.6);
            margin: 2px 0 0;
        }
        .sfa-header-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .sfa-header-meta {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 2px;
        }
        .sfa-header-term {
            font-size: 0.7rem;
            font-weight: 600;
            color: rgba(255,255,255,0.5);
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }
        .sfa-header-time {
            font-size: 1.5rem;
            font-weight: 700;
            color: #ffffff;
            line-height: 1;
            font-variant-numeric: tabular-nums;
        }
        .sfa-header-motto {
            font-size: 0.65rem;
            font-weight: 500;
            color: rgba(255,255,255,0.45);
            padding: 4px 12px;
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 20px;
            white-space: nowrap;
            letter-spacing: 0.02em;
        }
        .sfa-header-stripe {
            height: 3px;
            background: linear-gradient(90deg, var(--red) 0%, var(--red-dark) 50%, transparent 100%);
        }
        @media (max-width: 640px) {
            .sfa-header-motto,
            .sfa-header-time { display: none; }
        }

        /* ---- QUICK ACTIONS ---- */
        .sfa-actions-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .sfa-action-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            border-radius: 8px;
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            font-size: 0.8rem;
            font-weight: 500;
            color: var(--text-primary);
            text-decoration: none;
            transition: all 0.15s ease;
        }
        .sfa-action-pill:hover {
            border-color: var(--navy);
            box-shadow: 0 2px 8px rgba(30,58,95,0.1);
            transform: translateY(-1px);
        }
        .sfa-action-icon {
            width: 16px;
            height: 16px;
            color: var(--navy);
            flex-shrink: 0;
        }

        /* ---- KPI STRIP ---- */
        .sfa-kpi-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 12px;
        }
        @media (max-width: 1024px) {
            .sfa-kpi-grid { grid-template-columns: repeat(3, 1fr); }
        }
        @media (max-width: 640px) {
            .sfa-kpi-grid { grid-template-columns: repeat(2, 1fr); }
        }
        .sfa-kpi-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-left: 3px solid var(--kpi-accent);
            border-radius: 10px;
            padding: 14px 16px;
            transition: box-shadow 0.2s ease;
        }
        .sfa-kpi-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        }
        .sfa-kpi-top {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
        }
        .sfa-kpi-icon-wrap {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: color-mix(in srgb, var(--kpi-accent) 10%, transparent);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .sfa-kpi-icon {
            width: 16px;
            height: 16px;
            color: var(--kpi-accent);
        }
        .sfa-kpi-label {
            font-size: 0.7rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .sfa-kpi-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            line-height: 1.1;
            font-variant-numeric: tabular-nums;
        }
        .sfa-kpi-sub {
            font-size: 0.7rem;
            color: var(--text-tertiary);
            margin-top: 4px;
        }

        /* ---- ATTENTION BAR ---- */
        .sfa-attention-bar {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-left: 3px solid var(--amber);
            border-radius: 10px;
            padding: 14px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }
        .sfa-attention-header {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .sfa-attention-icon {
            width: 18px;
            height: 18px;
            color: var(--amber);
            flex-shrink: 0;
        }
        .sfa-attention-title {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-primary);
        }
        .sfa-attention-items {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .sfa-attention-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .sfa-attention-chip--amber { background: #fef3c7; color: #92400e; }
        .sfa-attention-chip--orange { background: #ffedd5; color: #9a3412; }
        .sfa-attention-chip--red { background: #fee2e2; color: #991b1b; }
        .sfa-attention-chip--gray { background: #f3f4f6; color: #4b5563; }
        .dark .sfa-attention-chip--amber { background: rgba(217,119,6,0.15); color: #fbbf24; }
        .dark .sfa-attention-chip--orange { background: rgba(234,88,12,0.15); color: #fb923c; }
        .dark .sfa-attention-chip--red { background: rgba(220,38,38,0.15); color: #f87171; }
        .dark .sfa-attention-chip--gray { background: rgba(107,114,128,0.15); color: #9ca3af; }
        .sfa-attention-count {
            font-weight: 700;
            font-size: 0.85rem;
        }

        /* ---- CARD (generic) ---- */
        .sfa-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 10px;
            overflow: hidden;
        }
        .sfa-card-head {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 14px 18px;
            border-bottom: 1px solid var(--card-border);
        }
        .sfa-card-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
        }
        .sfa-card-title {
            font-size: 0.825rem;
            font-weight: 600;
            color: var(--text-primary);
            flex: 1;
            margin: 0;
        }
        .sfa-card-badge {
            font-size: 0.68rem;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 4px;
            background: rgba(30,58,95,0.08);
            color: var(--navy);
            white-space: nowrap;
        }
        .dark .sfa-card-badge {
            background: rgba(147,197,253,0.1);
            color: #93c5fd;
        }
        .sfa-card-badge--red {
            background: #fee2e2;
            color: #991b1b;
        }
        .dark .sfa-card-badge--red {
            background: rgba(220,38,38,0.15);
            color: #f87171;
        }
        .sfa-card-link {
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--navy);
            text-decoration: none;
            opacity: 0.7;
            transition: opacity 0.15s;
        }
        .sfa-card-link:hover { opacity: 1; }
        .dark .sfa-card-link { color: #93c5fd; }
        .sfa-card-body {
            padding: 16px 18px;
        }

        /* ---- TABLE ---- */
        .sfa-table-wrap {
            overflow-x: auto;
        }
        .sfa-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.825rem;
        }
        .sfa-table th {
            padding: 10px 18px;
            text-align: left;
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-secondary);
            background: var(--surface);
            border-bottom: 1px solid var(--card-border);
        }
        .sfa-table td {
            padding: 10px 18px;
            border-bottom: 1px solid color-mix(in srgb, var(--card-border) 50%, transparent);
            color: var(--text-primary);
        }
        .sfa-table tbody tr:hover {
            background: color-mix(in srgb, var(--navy) 3%, transparent);
        }
        .dark .sfa-table tbody tr:hover {
            background: rgba(255,255,255,0.03);
        }
        .sfa-table-name { font-weight: 600; }
        .sfa-table-mono {
            font-family: 'DM Sans', monospace;
            font-size: 0.75rem;
            color: var(--text-secondary);
        }

        /* Rank badges */
        .sfa-rank {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 26px;
            height: 26px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 700;
            background: var(--surface);
            color: var(--text-secondary);
        }
        .sfa-rank--1 { background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; }
        .sfa-rank--2 { background: linear-gradient(135deg, #9ca3af, #6b7280); color: #fff; }
        .sfa-rank--3 { background: linear-gradient(135deg, #b45309, #92400e); color: #fff; }

        /* Average badges */
        .sfa-avg-badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 4px;
            font-size: 0.78rem;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
        }
        .sfa-avg--high { background: #ecfdf5; color: #065f46; }
        .sfa-avg--mid { background: #eff6ff; color: #1e40af; }
        .sfa-avg--low { background: #fef3c7; color: #92400e; }
        .dark .sfa-avg--high { background: rgba(5,150,105,0.15); color: #6ee7b7; }
        .dark .sfa-avg--mid { background: rgba(59,130,246,0.15); color: #93c5fd; }
        .dark .sfa-avg--low { background: rgba(217,119,6,0.15); color: #fbbf24; }

        /* ---- MINI LIST PANELS ---- */
        .sfa-card--mini .sfa-list { max-height: 320px; overflow-y: auto; }
        .sfa-list-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 18px;
            gap: 12px;
            border-bottom: 1px solid color-mix(in srgb, var(--card-border) 50%, transparent);
            transition: background 0.1s;
        }
        .sfa-list-item:hover {
            background: color-mix(in srgb, var(--navy) 2%, transparent);
        }
        .dark .sfa-list-item:hover {
            background: rgba(255,255,255,0.02);
        }
        .sfa-list-item:last-child { border-bottom: none; }
        .sfa-list-main {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }
        .sfa-list-name {
            font-size: 0.825rem;
            font-weight: 600;
            color: var(--text-primary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sfa-list-meta {
            font-size: 0.7rem;
            color: var(--text-tertiary);
            margin-top: 1px;
        }
        .sfa-list-end {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            flex-shrink: 0;
        }
        .sfa-list-amount {
            font-size: 0.825rem;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
        }
        .sfa-list-amount--green { color: var(--green); }
        .sfa-list-amount--critical { color: var(--red); }
        .sfa-list-amount--warning { color: var(--amber); }
        .sfa-list-amount--normal { color: var(--text-secondary); }
        .sfa-list-status {
            font-size: 0.65rem;
            font-weight: 500;
            color: var(--text-tertiary);
        }
        .sfa-list-status--paid { color: var(--green); }
        .sfa-list-status--partial { color: var(--amber); }
        .sfa-list-time {
            font-size: 0.68rem;
            color: var(--text-tertiary);
            white-space: nowrap;
        }
        .sfa-list-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 32px 16px;
            gap: 8px;
            color: var(--text-tertiary);
            font-size: 0.8rem;
        }
        .sfa-list-empty-icon {
            width: 28px;
            height: 28px;
            opacity: 0.4;
        }

        /* ---- EVENTS ---- */
        .sfa-events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 10px;
            padding: 14px 18px;
        }
        .sfa-event-card {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: 8px;
            background: var(--surface);
            transition: background 0.1s;
        }
        .sfa-event-card:hover {
            background: color-mix(in srgb, var(--navy) 4%, var(--surface));
        }
        .sfa-event-date {
            display: flex;
            flex-direction: column;
            align-items: center;
            min-width: 42px;
            padding: 6px 8px;
            border-radius: 8px;
            background: var(--red);
            color: #fff;
            flex-shrink: 0;
        }
        .sfa-event-day {
            font-size: 1rem;
            font-weight: 700;
            line-height: 1;
        }
        .sfa-event-month {
            font-size: 0.6rem;
            font-weight: 600;
            text-transform: uppercase;
            opacity: 0.85;
        }
        .sfa-event-info {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }
        .sfa-event-title {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-primary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sfa-event-loc {
            font-size: 0.7rem;
            color: var(--text-tertiary);
            margin-top: 1px;
        }

        /* ---- SCROLLBAR ---- */
        .sfa-list::-webkit-scrollbar { width: 4px; }
        .sfa-list::-webkit-scrollbar-track { background: transparent; }
        .sfa-list::-webkit-scrollbar-thumb { background: rgba(156,163,175,0.3); border-radius: 2px; }
        .sfa-list::-webkit-scrollbar-thumb:hover { background: rgba(156,163,175,0.5); }

        /* ---- ATTENDANCE REGISTER TABLE ---- */
        .sfa-reg-scroll {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .sfa-reg {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.78rem;
            white-space: nowrap;
            min-width: 680px;
        }
        .sfa-reg thead th {
            background: var(--navy);
            color: #fff;
            padding: 0;
            text-align: center;
            font-weight: 600;
            border: 1px solid rgba(255,255,255,0.15);
        }
        .sfa-reg-grade {
            padding: 10px 14px !important;
            text-align: left !important;
            min-width: 110px;
            position: sticky;
            left: 0;
            z-index: 2;
            background: var(--navy) !important;
        }
        .sfa-reg-group {
            padding: 8px 6px !important;
            font-size: 0.72rem;
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }
        .sfa-reg-group--total {
            background: rgba(255,255,255,0.08) !important;
        }
        .sfa-reg-sub {
            padding: 5px 4px !important;
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            min-width: 32px;
        }
        .sfa-reg-sub--boy {
            background: rgba(30,58,95,0.85) !important;
            color: #93c5fd;
        }
        .sfa-reg-sub--girl {
            background: rgba(185,28,28,0.7) !important;
            color: #fca5a5;
        }
        .sfa-reg-sub--all {
            background: rgba(255,255,255,0.12) !important;
        }
        .sfa-reg tbody td,
        .sfa-reg tfoot td {
            padding: 7px 6px;
            text-align: center;
            border-bottom: 1px solid var(--card-border);
            color: var(--text-primary);
            font-variant-numeric: tabular-nums;
        }
        .sfa-reg-name {
            text-align: left !important;
            font-weight: 600;
            padding-left: 14px !important;
            padding-right: 10px !important;
            position: sticky;
            left: 0;
            background: var(--card-bg);
            z-index: 1;
            min-width: 110px;
        }
        .sfa-reg-val {
            font-size: 0.78rem;
            color: var(--text-secondary);
        }
        .sfa-reg-val--total {
            font-weight: 700;
            color: var(--text-primary);
            background: color-mix(in srgb, var(--navy) 4%, transparent);
        }
        .dark .sfa-reg-val--total {
            background: rgba(255,255,255,0.04);
        }
        .sfa-reg-val--grand {
            font-weight: 800;
            color: var(--navy);
            background: color-mix(in srgb, var(--navy) 8%, transparent);
            font-size: 0.85rem;
        }
        .dark .sfa-reg-val--grand {
            color: #93c5fd;
            background: rgba(147,197,253,0.08);
        }
        .sfa-reg tbody tr:hover td {
            background: color-mix(in srgb, var(--navy) 3%, transparent);
        }
        .sfa-reg tbody tr:hover .sfa-reg-name {
            background: color-mix(in srgb, var(--navy) 3%, var(--card-bg));
        }
        .dark .sfa-reg tbody tr:hover td {
            background: rgba(255,255,255,0.03);
        }
        .dark .sfa-reg tbody tr:hover .sfa-reg-name {
            background: color-mix(in srgb, rgba(255,255,255,0.03), var(--card-bg));
        }
        .sfa-reg-empty td {
            color: var(--text-tertiary) !important;
        }
        .sfa-reg-footer td {
            font-weight: 700 !important;
            color: var(--text-primary) !important;
            background: var(--surface) !important;
            border-top: 2px solid var(--navy);
            font-size: 0.82rem;
        }
        .sfa-reg-footer .sfa-reg-name {
            background: var(--surface) !important;
            font-weight: 800 !important;
            text-transform: uppercase;
            font-size: 0.72rem;
            letter-spacing: 0.05em;
        }
        .sfa-reg tbody tr:nth-child(even) td {
            background: color-mix(in srgb, var(--surface) 50%, var(--card-bg));
        }
        .sfa-reg tbody tr:nth-child(even) .sfa-reg-name {
            background: color-mix(in srgb, var(--surface) 50%, var(--card-bg));
        }

        /* ---- MONTH COMPARISON ---- */
        .sfa-compare-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }
        @media (max-width: 768px) {
            .sfa-compare-grid { grid-template-columns: 1fr; }
        }
        .sfa-compare-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 16px;
            border-radius: 10px;
            background: var(--surface);
        }
        .sfa-compare-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: color-mix(in srgb, var(--ci-color) 10%, transparent);
            color: var(--ci-color);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .sfa-compare-data {
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 0;
        }
        .sfa-compare-label {
            font-size: 0.7rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .sfa-compare-values {
            display: flex;
            align-items: baseline;
            gap: 6px;
            flex-wrap: wrap;
        }
        .sfa-compare-current {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
            font-variant-numeric: tabular-nums;
        }
        .sfa-compare-vs {
            font-size: 0.72rem;
            color: var(--text-tertiary);
        }
        .sfa-compare-change {
            font-size: 0.7rem;
            font-weight: 700;
            padding: 1px 6px;
            border-radius: 4px;
        }
        .sfa-compare-change--up {
            background: #ecfdf5;
            color: #065f46;
        }
        .sfa-compare-change--down {
            background: #fef2f2;
            color: #991b1b;
        }
        .dark .sfa-compare-change--up {
            background: rgba(5,150,105,0.15);
            color: #6ee7b7;
        }
        .dark .sfa-compare-change--down {
            background: rgba(220,38,38,0.15);
            color: #fca5a5;
        }

        /* ---- GENDER DISTRIBUTION ---- */
        .sfa-gender-layout {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 24px;
            align-items: center;
        }
        @media (max-width: 480px) {
            .sfa-gender-layout { grid-template-columns: 1fr; }
        }
        .sfa-gender-legend {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .sfa-gender-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sfa-gender-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }
        .sfa-gender-label {
            font-size: 0.825rem;
            color: var(--text-secondary);
            flex: 1;
        }
        .sfa-gender-value {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-primary);
            font-variant-numeric: tabular-nums;
            min-width: 36px;
            text-align: right;
        }
        .sfa-gender-pct {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-tertiary);
            min-width: 36px;
            text-align: right;
        }

        /* ---- GRADE CAPACITY ---- */
        .sfa-capacity-body {
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-height: 320px;
            overflow-y: auto;
        }
        .sfa-cap-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sfa-cap-info {
            display: flex;
            flex-direction: column;
            min-width: 90px;
            flex-shrink: 0;
        }
        .sfa-cap-name {
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--text-primary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sfa-cap-nums {
            font-size: 0.65rem;
            color: var(--text-tertiary);
            font-variant-numeric: tabular-nums;
        }
        .sfa-cap-bar-wrap {
            flex: 1;
            height: 10px;
            background: var(--surface);
            border-radius: 5px;
            overflow: hidden;
        }
        .sfa-cap-bar {
            height: 100%;
            border-radius: 5px;
            transition: width 0.5s ease;
            min-width: 2px;
        }
        .sfa-cap-bar--ok { background: var(--green); }
        .sfa-cap-bar--warning { background: var(--amber); }
        .sfa-cap-bar--danger { background: var(--red); }
        .sfa-cap-pct {
            font-size: 0.72rem;
            font-weight: 700;
            color: var(--text-secondary);
            min-width: 36px;
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        /* ---- LOAD ANIMATION ---- */
        @keyframes sfaFadeUp {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .sfa-dash > * {
            animation: sfaFadeUp 0.35s ease both;
        }
        .sfa-dash > *:nth-child(1) { animation-delay: 0s; }
        .sfa-dash > *:nth-child(2) { animation-delay: 0.04s; }
        .sfa-dash > *:nth-child(3) { animation-delay: 0.08s; }
        .sfa-dash > *:nth-child(4) { animation-delay: 0.12s; }
        .sfa-dash > *:nth-child(5) { animation-delay: 0.16s; }
        .sfa-dash > *:nth-child(6) { animation-delay: 0.20s; }
        .sfa-dash > *:nth-child(7) { animation-delay: 0.24s; }
        .sfa-dash > *:nth-child(8) { animation-delay: 0.28s; }
        .sfa-dash > *:nth-child(9) { animation-delay: 0.32s; }
        .sfa-dash > *:nth-child(10) { animation-delay: 0.36s; }
        .sfa-dash > *:nth-child(11) { animation-delay: 0.40s; }
        .sfa-dash > *:nth-child(12) { animation-delay: 0.44s; }
    </style>

    {{-- ============================================================
         CHARTS — ApexCharts
         ============================================================ --}}
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        let chartInstances = {};

        function initDashboardCharts() {
            const isDark = document.documentElement.classList.contains('dark');
            const txt = isDark ? '#9CA3AF' : '#6B7280';
            const grid = isDark ? '#374151' : '#f1f5f9';
            const bg = 'transparent';
            const navy = '#1e3a5f';
            const red = '#dc2626';

            // Destroy existing
            Object.values(chartInstances).forEach(c => { if (c?.destroy) c.destroy(); });
            chartInstances = {};

            // Shared config
            const shared = {
                chart: { background: bg, fontFamily: 'DM Sans, sans-serif' },
                grid: { borderColor: grid, strokeDashArray: 3 },
            };

            // ---- Enrollment Donut ----
            const enrollData = @json($chartData['gradeData'] ?? []);
            const enrollEl = document.querySelector('#chart-enrollment');
            if (enrollData.length > 0 && enrollEl) {
                chartInstances.enroll = new ApexCharts(enrollEl, {
                    ...shared,
                    series: enrollData.map(i => i.count),
                    labels: enrollData.map(i => i.grade),
                    chart: { ...shared.chart, type: 'donut', height: 240 },
                    colors: ['#1e3a5f', '#dc2626', '#2c5282', '#b91c1c', '#3b6998', '#059669', '#d97706', '#7c3aed', '#334e75', '#991b1b'],
                    stroke: { width: 2, colors: [isDark ? '#1f2937' : '#fff'] },
                    legend: { position: 'right', fontSize: '12px', labels: { colors: txt }, offsetY: 0, itemMargin: { vertical: 4 } },
                    dataLabels: { enabled: false },
                    plotOptions: { pie: { donut: { size: '72%', labels: { show: true, total: { show: true, label: 'Total', color: txt, fontSize: '11px', fontWeight: 600 }, value: { fontSize: '20px', fontWeight: 700, color: isDark ? '#f9fafb' : '#111827' } } } } },
                    responsive: [{ breakpoint: 480, options: { legend: { position: 'bottom' } } }]
                });
                chartInstances.enroll.render();
            }

            // ---- Fee Area ----
            const feeData = @json($financialSummary['monthly'] ?? []);
            const feeEl = document.querySelector('#chart-fees');
            if (feeData.length > 0 && feeEl) {
                chartInstances.fees = new ApexCharts(feeEl, {
                    ...shared,
                    series: [{ name: 'Collected (ZMW)', data: feeData.map(i => i.collected) }],
                    chart: { ...shared.chart, type: 'area', height: 240, toolbar: { show: false }, sparkline: { enabled: false } },
                    stroke: { curve: 'smooth', width: 2.5 },
                    fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.45, opacityTo: 0.05, stops: [0, 95, 100] } },
                    colors: ['#059669'],
                    xaxis: { categories: feeData.map(i => i.month), labels: { style: { colors: txt, fontSize: '11px' } }, axisBorder: { show: false }, axisTicks: { show: false } },
                    yaxis: { labels: { style: { colors: txt, fontSize: '11px' }, formatter: v => v > 0 ? 'K' + (v >= 1000 ? (v/1000).toFixed(0) + 'k' : v) : '0' } },
                    tooltip: { y: { formatter: v => 'K' + v.toLocaleString() } },
                    markers: { size: 4, strokeWidth: 0 }
                });
                chartInstances.fees.render();
            }

            // ---- Gender Donut ----
            const genderData = @json($genderStats ?? []);
            const genderEl = document.querySelector('#chart-gender');
            if (genderEl && genderData.total > 0) {
                const gSeries = [genderData.male, genderData.female];
                const gLabels = ['Male', 'Female'];
                if (genderData.other > 0) { gSeries.push(genderData.other); gLabels.push('Other'); }
                chartInstances.gender = new ApexCharts(genderEl, {
                    series: gSeries,
                    labels: gLabels,
                    chart: { type: 'donut', height: 200, background: bg, fontFamily: 'DM Sans, sans-serif' },
                    colors: ['#1e3a5f', '#dc2626', '#6b7280'],
                    stroke: { width: 2, colors: [isDark ? '#1f2937' : '#fff'] },
                    legend: { show: false },
                    dataLabels: { enabled: false },
                    plotOptions: { pie: { donut: { size: '70%', labels: { show: true, total: { show: true, label: 'Total', color: txt, fontSize: '10px', fontWeight: 600 }, value: { fontSize: '18px', fontWeight: 700, color: isDark ? '#f9fafb' : '#111827' } } } } },
                });
                chartInstances.gender.render();
            }

            // ---- Subject Bar ----
            const subData = @json($chartData['resultData'] ?? []);
            const subEl = document.querySelector('#chart-subjects');
            if (subData.length > 0 && subEl) {
                chartInstances.subj = new ApexCharts(subEl, {
                    ...shared,
                    series: [{ name: 'Avg %', data: subData.map(i => parseFloat(i.average).toFixed(1)) }],
                    chart: { ...shared.chart, type: 'bar', height: 240, toolbar: { show: false } },
                    plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '65%', distributed: true } },
                    colors: ['#1e3a5f', '#dc2626', '#2c5282', '#059669', '#d97706', '#7c3aed'],
                    xaxis: {
                        categories: subData.map(i => i.name.length > 14 ? i.name.substring(0, 14) + '..' : i.name),
                        labels: { style: { colors: txt, fontSize: '11px' }, formatter: v => v + '%' },
                        axisBorder: { show: false }, axisTicks: { show: false }
                    },
                    yaxis: { labels: { style: { colors: txt, fontSize: '11px' } } },
                    dataLabels: { enabled: true, formatter: v => v + '%', style: { fontSize: '11px', fontWeight: 600, colors: ['#fff'] }, offsetX: -4 },
                    legend: { show: false },
                    tooltip: { enabled: false }
                });
                chartInstances.subj.render();
            }
        }

        document.addEventListener('DOMContentLoaded', initDashboardCharts);
        new MutationObserver(ms => ms.forEach(m => m.attributeName === 'class' && setTimeout(initDashboardCharts, 150)))
            .observe(document.documentElement, { attributes: true });
    </script>
    @endpush
</x-filament-panels::page>
