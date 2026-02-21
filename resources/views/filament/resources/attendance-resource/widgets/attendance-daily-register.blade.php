@php
    $data = $data ?? $this->getRegisterData();
    $register = $data['register'] ?? [];
    $summary = $data['summary'] ?? [];
    $dateDisplay = $data['date_display'] ?? '';
    $isToday = $data['is_today'] ?? false;
    $classStudents = $classStudents ?? [];
    $selectedClassInfo = $selectedClassInfo ?? null;
    $selectedId = $this->selectedClassSectionId;
@endphp

<x-filament-widgets::widget>
    <div class="sfa-daily-register">

        {{-- Date Navigation Strip --}}
        <div class="sfa-dr-nav">
            <div class="sfa-dr-nav-left">
                <button wire:click="previousDay" type="button" class="sfa-dr-nav-btn" title="Previous Day">
                    <x-heroicon-o-chevron-left class="w-4 h-4" />
                </button>

                <div class="sfa-dr-date-picker-wrap">
                    <input
                        type="date"
                        wire:model.live="selectedDate"
                        max="{{ now()->toDateString() }}"
                        class="sfa-dr-date-input"
                    />
                </div>

                <button wire:click="nextDay" type="button" class="sfa-dr-nav-btn" title="Next Day"
                    @if($isToday) disabled @endif>
                    <x-heroicon-o-chevron-right class="w-4 h-4" />
                </button>

                @if(!$isToday)
                    <button wire:click="goToToday" type="button" class="sfa-dr-today-btn">
                        Today
                    </button>
                @endif
            </div>
            <div class="sfa-dr-nav-right">
                <span class="sfa-dr-date-label">{{ $dateDisplay }}</span>
            </div>
        </div>

        {{-- Summary Stats Row --}}
        <div class="sfa-dr-stats">
            <div class="sfa-dr-stat">
                <span class="sfa-dr-stat-value">{{ $summary['total_classes'] ?? 0 }}</span>
                <span class="sfa-dr-stat-label">Total Classes</span>
            </div>
            <div class="sfa-dr-stat sfa-dr-stat--green">
                <span class="sfa-dr-stat-value">{{ $summary['fully_marked'] ?? 0 }}</span>
                <span class="sfa-dr-stat-label">Fully Marked</span>
            </div>
            <div class="sfa-dr-stat sfa-dr-stat--yellow">
                <span class="sfa-dr-stat-value">{{ $summary['partially_marked'] ?? 0 }}</span>
                <span class="sfa-dr-stat-label">Partial</span>
            </div>
            <div class="sfa-dr-stat sfa-dr-stat--gray">
                <span class="sfa-dr-stat-value">{{ $summary['not_started'] ?? 0 }}</span>
                <span class="sfa-dr-stat-label">Not Started</span>
            </div>
        </div>

        {{-- Register Table --}}
        <div class="sfa-dr-table-wrap">
            <table class="sfa-dr-table">
                <thead>
                    <tr>
                        <th class="sfa-dr-th-class">Class</th>
                        <th class="sfa-dr-th-teacher">Grade Teacher</th>
                        <th class="sfa-dr-th-num">Students</th>
                        <th class="sfa-dr-th-num sfa-dr-th--present">Present</th>
                        <th class="sfa-dr-th-num sfa-dr-th--absent">Absent</th>
                        <th class="sfa-dr-th-num sfa-dr-th--sick">Sick</th>
                        <th class="sfa-dr-th-num sfa-dr-th--late">Late</th>
                        <th class="sfa-dr-th-num sfa-dr-th--excused">Excused</th>
                        <th class="sfa-dr-th-num">Marked</th>
                        <th class="sfa-dr-th-status">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($register as $row)
                        <tr wire:click="selectClass({{ $row['class_section_id'] }})"
                            class="sfa-dr-row sfa-dr-row--{{ $row['status_color'] }} {{ $selectedId === $row['class_section_id'] ? 'sfa-dr-row--selected' : '' }}"
                            style="cursor: pointer;">
                            <td class="sfa-dr-td-class">{{ $row['class_name'] }}</td>
                            <td class="sfa-dr-td-teacher">{{ $row['grade_teacher'] }}</td>
                            <td class="sfa-dr-td-num">{{ $row['total_students'] }}</td>
                            <td class="sfa-dr-td-num sfa-dr-td--present">{{ $row['present'] ?: '-' }}</td>
                            <td class="sfa-dr-td-num sfa-dr-td--absent">{{ $row['absent'] ?: '-' }}</td>
                            <td class="sfa-dr-td-num sfa-dr-td--sick">{{ $row['sick'] ?: '-' }}</td>
                            <td class="sfa-dr-td-num sfa-dr-td--late">{{ $row['late'] ?: '-' }}</td>
                            <td class="sfa-dr-td-num sfa-dr-td--excused">{{ $row['excused'] ?: '-' }}</td>
                            <td class="sfa-dr-td-num sfa-dr-td-marked">
                                {{ $row['marked_count'] }}/{{ $row['total_students'] }}
                                <span class="sfa-dr-pct">({{ $row['percent_marked'] }}%)</span>
                            </td>
                            <td class="sfa-dr-td-status">
                                <span class="sfa-dr-badge sfa-dr-badge--{{ $row['status_color'] }}">
                                    {{ $row['status_label'] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="sfa-dr-empty">
                                <x-heroicon-o-clipboard-document-check class="w-8 h-8 opacity-30" />
                                <span>No class sections found</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Detail Panel --}}
        @if($selectedId && $selectedClassInfo)
            <div class="sfa-dr-detail">
                {{-- Tab Strip --}}
                <div class="sfa-dr-tabs">
                    @foreach($register as $row)
                        @if($row['total_students'] > 0)
                            <button
                                wire:click="selectClass({{ $row['class_section_id'] }})"
                                type="button"
                                class="sfa-dr-tab {{ $selectedId === $row['class_section_id'] ? 'sfa-dr-tab--active' : '' }}">
                                {{ $row['class_name'] }}
                            </button>
                        @endif
                    @endforeach
                </div>

                {{-- Class Info Bar --}}
                <div class="sfa-dr-class-info">
                    <div class="sfa-dr-class-info-item">
                        <span class="sfa-dr-class-info-label">Class</span>
                        <span class="sfa-dr-class-info-value">{{ $selectedClassInfo['class_name'] }}</span>
                    </div>
                    <div class="sfa-dr-class-info-item">
                        <span class="sfa-dr-class-info-label">Grade Teacher</span>
                        <span class="sfa-dr-class-info-value">{{ $selectedClassInfo['grade_teacher'] }}</span>
                    </div>
                    <div class="sfa-dr-class-info-item">
                        <span class="sfa-dr-class-info-label">Date</span>
                        <span class="sfa-dr-class-info-value">{{ $dateDisplay }}</span>
                    </div>
                    <div class="sfa-dr-class-info-item">
                        <span class="sfa-dr-class-info-label">Students</span>
                        <span class="sfa-dr-class-info-value">{{ $selectedClassInfo['student_count'] }}</span>
                    </div>
                    <div class="sfa-dr-class-info-actions">
                        <a href="{{ route('attendance.register.download', ['class_section_id' => $selectedId]) }}"
                           target="_blank"
                           class="sfa-dr-download-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            PDF
                        </a>
                        <a href="{{ route('attendance.register.download-excel', ['class_section_id' => $selectedId]) }}"
                           class="sfa-dr-download-btn" style="background: #065f46;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                            Excel
                        </a>
                    </div>
                </div>

                {{-- Student Table --}}
                <div class="sfa-dr-detail-table-wrap">
                    <table class="sfa-dr-detail-table">
                        <thead>
                            <tr>
                                <th class="sfa-dr-detail-th-num">#</th>
                                <th class="sfa-dr-detail-th-name">Student Name</th>
                                <th class="sfa-dr-detail-th-dob">DOB</th>
                                <th class="sfa-dr-detail-th-gender">G</th>
                                <th class="sfa-dr-detail-th-parent">Parent Name</th>
                                <th class="sfa-dr-detail-th-phone">Phone</th>
                                <th class="sfa-dr-detail-th-status">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($classStudents as $index => $student)
                                <tr class="sfa-dr-detail-row">
                                    <td class="sfa-dr-detail-td-num">{{ $index + 1 }}</td>
                                    <td class="sfa-dr-detail-td-name">{{ $student['student_name'] }}</td>
                                    <td class="sfa-dr-detail-td-dob">{{ $student['date_of_birth'] }}</td>
                                    <td class="sfa-dr-detail-td-gender">{{ $student['gender'] }}</td>
                                    <td class="sfa-dr-detail-td-parent">{{ $student['parent_name'] }}</td>
                                    <td class="sfa-dr-detail-td-phone">{{ $student['parent_phone'] }}</td>
                                    <td class="sfa-dr-detail-td-status">
                                        <span class="sfa-dr-status-badge sfa-dr-status-badge--{{ $student['status_key'] }}">
                                            {{ $student['status'] }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="sfa-dr-empty">
                                        <span>No students enrolled in this class</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Summary Line --}}
                @if(count($classStudents) > 0)
                    @php
                        $presentCount = collect($classStudents)->where('status_key', 'present')->count();
                        $absentCount = collect($classStudents)->where('status_key', 'absent')->count();
                        $sickCount = collect($classStudents)->where('status_key', 'sick')->count();
                        $lateCount = collect($classStudents)->where('status_key', 'late')->count();
                        $excusedCount = collect($classStudents)->where('status_key', 'excused')->count();
                        $notMarkedCount = collect($classStudents)->where('status_key', 'not_marked')->count();
                    @endphp
                    <div class="sfa-dr-detail-summary">
                        <span class="sfa-dr-detail-summary-item sfa-dr-detail-summary--present">P: {{ $presentCount }}</span>
                        <span class="sfa-dr-detail-summary-item sfa-dr-detail-summary--absent">X: {{ $absentCount }}</span>
                        <span class="sfa-dr-detail-summary-item sfa-dr-detail-summary--sick">S: {{ $sickCount }}</span>
                        <span class="sfa-dr-detail-summary-item sfa-dr-detail-summary--late">Y: {{ $lateCount }}</span>
                        <span class="sfa-dr-detail-summary-item sfa-dr-detail-summary--excused">L: {{ $excusedCount }}</span>
                        @if($notMarkedCount > 0)
                            <span class="sfa-dr-detail-summary-item sfa-dr-detail-summary--not-marked">Not Marked: {{ $notMarkedCount }}</span>
                        @endif
                    </div>
                @endif
            </div>
        @endif

    </div>

    <style>
        .sfa-daily-register {
            font-family: 'DM Sans', ui-sans-serif, system-ui, sans-serif;
            --navy: #1e3a5f;
            --red: #dc2626;
            --green: #059669;
            --amber: #d97706;
            --purple: #7c3aed;
            --card-bg: #ffffff;
            --card-border: #e5e7eb;
            --text-primary: #111827;
            --text-secondary: #6b7280;
            --text-tertiary: #9ca3af;
            --surface: #f8fafc;
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 10px;
            overflow: hidden;
        }
        .dark .sfa-daily-register {
            --card-bg: #1f2937;
            --card-border: #374151;
            --text-primary: #f9fafb;
            --text-secondary: #9ca3af;
            --text-tertiary: #6b7280;
            --surface: #111827;
        }

        /* ---- NAV STRIP ---- */
        .sfa-dr-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 18px;
            border-bottom: 1px solid var(--card-border);
            background: var(--navy);
            gap: 12px;
            flex-wrap: wrap;
        }
        .sfa-dr-nav-left {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .sfa-dr-nav-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 6px;
            background: rgba(255,255,255,0.12);
            color: #ffffff;
            border: 1px solid rgba(255,255,255,0.2);
            cursor: pointer;
            transition: all 0.15s;
        }
        .sfa-dr-nav-btn:hover:not([disabled]) {
            background: rgba(255,255,255,0.22);
        }
        .sfa-dr-nav-btn[disabled] {
            opacity: 0.35;
            cursor: not-allowed;
        }
        .sfa-dr-date-picker-wrap {
            position: relative;
        }
        .sfa-dr-date-input {
            padding: 5px 10px;
            border-radius: 6px;
            border: 1px solid rgba(255,255,255,0.25);
            background: rgba(255,255,255,0.1);
            color: #ffffff;
            font-size: 0.82rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            outline: none;
        }
        .sfa-dr-date-input:focus {
            border-color: rgba(255,255,255,0.5);
            background: rgba(255,255,255,0.18);
        }
        .sfa-dr-date-input::-webkit-calendar-picker-indicator {
            filter: invert(1);
            cursor: pointer;
        }
        .sfa-dr-today-btn {
            padding: 5px 14px;
            border-radius: 6px;
            background: var(--red);
            color: #ffffff;
            border: none;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s;
            font-family: inherit;
        }
        .sfa-dr-today-btn:hover {
            background: #b91c1c;
        }
        .sfa-dr-nav-right {
            display: flex;
            align-items: center;
        }
        .sfa-dr-date-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: rgba(255,255,255,0.85);
        }

        /* ---- SUMMARY STATS ---- */
        .sfa-dr-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0;
            border-bottom: 1px solid var(--card-border);
        }
        @media (max-width: 640px) {
            .sfa-dr-stats { grid-template-columns: repeat(2, 1fr); }
        }
        .sfa-dr-stat {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 14px 10px;
            border-right: 1px solid var(--card-border);
        }
        .sfa-dr-stat:last-child { border-right: none; }
        .sfa-dr-stat-value {
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--navy);
            line-height: 1;
            font-variant-numeric: tabular-nums;
        }
        .sfa-dr-stat--green .sfa-dr-stat-value { color: var(--green); }
        .sfa-dr-stat--yellow .sfa-dr-stat-value { color: var(--amber); }
        .sfa-dr-stat--gray .sfa-dr-stat-value { color: var(--text-tertiary); }
        .dark .sfa-dr-stat-value { color: #93c5fd; }
        .dark .sfa-dr-stat--green .sfa-dr-stat-value { color: #6ee7b7; }
        .dark .sfa-dr-stat--yellow .sfa-dr-stat-value { color: #fbbf24; }
        .dark .sfa-dr-stat--gray .sfa-dr-stat-value { color: var(--text-tertiary); }
        .sfa-dr-stat-label {
            font-size: 0.68rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-top: 4px;
        }

        /* ---- TABLE ---- */
        .sfa-dr-table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .sfa-dr-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8rem;
            min-width: 620px;
        }
        .sfa-dr-table thead th {
            padding: 10px 14px;
            text-align: left;
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-secondary);
            background: var(--surface);
            border-bottom: 1px solid var(--card-border);
        }
        .sfa-dr-th-teacher, .sfa-dr-td-teacher {
            white-space: nowrap;
            font-size: 0.78rem;
            color: var(--text-secondary);
        }
        .sfa-dr-th-num, .sfa-dr-td-num { text-align: center !important; }
        .sfa-dr-th-status, .sfa-dr-td-status { text-align: center !important; }
        .sfa-dr-th--present { color: var(--green) !important; }
        .sfa-dr-th--absent { color: var(--red) !important; }
        .sfa-dr-th--sick { color: #2563eb !important; }
        .sfa-dr-th--late { color: var(--amber) !important; }
        .sfa-dr-th--excused { color: var(--purple) !important; }

        .sfa-dr-table tbody td {
            padding: 9px 14px;
            border-bottom: 1px solid color-mix(in srgb, var(--card-border) 50%, transparent);
            color: var(--text-primary);
            font-variant-numeric: tabular-nums;
        }
        .sfa-dr-td-class {
            font-weight: 600;
            white-space: nowrap;
        }
        .sfa-dr-td--present { color: var(--green); font-weight: 600; }
        .sfa-dr-td--absent { color: var(--red); font-weight: 600; }
        .sfa-dr-td--sick { color: #2563eb; font-weight: 600; }
        .sfa-dr-td--late { color: var(--amber); font-weight: 600; }
        .sfa-dr-td--excused { color: var(--purple); font-weight: 600; }
        .sfa-dr-td-marked { white-space: nowrap; }
        .sfa-dr-pct {
            font-size: 0.7rem;
            color: var(--text-tertiary);
            margin-left: 2px;
        }

        /* Row status colors */
        .sfa-dr-row--green {
            background: color-mix(in srgb, var(--green) 4%, var(--card-bg));
        }
        .sfa-dr-row--yellow {
            background: color-mix(in srgb, var(--amber) 4%, var(--card-bg));
        }
        .sfa-dr-row--gray td {
            color: var(--text-tertiary);
        }
        .dark .sfa-dr-row--green {
            background: rgba(5,150,105,0.06);
        }
        .dark .sfa-dr-row--yellow {
            background: rgba(217,119,6,0.06);
        }

        .sfa-dr-table tbody tr:hover {
            background: color-mix(in srgb, var(--navy) 8%, var(--card-bg)) !important;
        }
        .dark .sfa-dr-table tbody tr:hover {
            background: rgba(255,255,255,0.06) !important;
        }

        /* Selected row */
        .sfa-dr-row--selected {
            background: color-mix(in srgb, var(--navy) 10%, var(--card-bg)) !important;
            border-left: 3px solid var(--navy);
        }
        .dark .sfa-dr-row--selected {
            background: rgba(147,197,253,0.1) !important;
            border-left: 3px solid #93c5fd;
        }

        /* Status badges */
        .sfa-dr-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 5px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .sfa-dr-badge--green {
            background: #ecfdf5;
            color: #065f46;
        }
        .sfa-dr-badge--yellow {
            background: #fffbeb;
            color: #92400e;
        }
        .sfa-dr-badge--gray {
            background: #f3f4f6;
            color: #6b7280;
        }
        .dark .sfa-dr-badge--green {
            background: rgba(5,150,105,0.15);
            color: #6ee7b7;
        }
        .dark .sfa-dr-badge--yellow {
            background: rgba(217,119,6,0.15);
            color: #fbbf24;
        }
        .dark .sfa-dr-badge--gray {
            background: rgba(107,114,128,0.15);
            color: #9ca3af;
        }

        /* Empty state */
        .sfa-dr-empty {
            text-align: center;
            padding: 32px 16px !important;
            color: var(--text-tertiary);
            font-size: 0.82rem;
        }
        .sfa-dr-empty span { display: block; margin-top: 6px; }

        @media (max-width: 640px) {
            .sfa-dr-date-label { display: none; }
        }

        /* ======== DETAIL PANEL ======== */
        .sfa-dr-detail {
            border-top: 2px solid var(--navy);
        }
        .dark .sfa-dr-detail {
            border-top-color: #93c5fd;
        }

        /* Tab strip */
        .sfa-dr-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 0;
            background: var(--surface);
            border-bottom: 1px solid var(--card-border);
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .sfa-dr-tab {
            padding: 8px 16px;
            font-size: 0.72rem;
            font-weight: 600;
            font-family: inherit;
            color: var(--text-secondary);
            background: transparent;
            border: none;
            border-bottom: 2px solid transparent;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.15s;
        }
        .sfa-dr-tab:hover {
            color: var(--navy);
            background: color-mix(in srgb, var(--navy) 5%, transparent);
        }
        .dark .sfa-dr-tab:hover {
            color: #93c5fd;
            background: rgba(147,197,253,0.06);
        }
        .sfa-dr-tab--active {
            color: #ffffff !important;
            background: var(--navy) !important;
            border-bottom-color: var(--navy);
        }
        .dark .sfa-dr-tab--active {
            background: rgba(147,197,253,0.2) !important;
            color: #93c5fd !important;
            border-bottom-color: #93c5fd;
        }

        /* Class info bar */
        .sfa-dr-class-info {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            padding: 12px 18px;
            background: color-mix(in srgb, var(--navy) 4%, var(--card-bg));
            border-bottom: 1px solid var(--card-border);
        }
        .dark .sfa-dr-class-info {
            background: rgba(147,197,253,0.04);
        }
        .sfa-dr-class-info-item {
            display: flex;
            flex-direction: column;
            gap: 1px;
        }
        .sfa-dr-class-info-label {
            font-size: 0.62rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-tertiary);
        }
        .sfa-dr-class-info-value {
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--text-primary);
        }
        .sfa-dr-class-info-actions {
            margin-left: auto;
        }
        .sfa-dr-download-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 16px;
            border-radius: 6px;
            background: var(--navy);
            color: #ffffff;
            font-size: 0.75rem;
            font-weight: 600;
            font-family: inherit;
            text-decoration: none;
            transition: background 0.15s;
        }
        .sfa-dr-download-btn:hover {
            background: #15304f;
            color: #ffffff;
        }
        .dark .sfa-dr-download-btn {
            background: rgba(147,197,253,0.18);
            color: #93c5fd;
        }
        .dark .sfa-dr-download-btn:hover {
            background: rgba(147,197,253,0.28);
        }

        /* Detail student table */
        .sfa-dr-detail-table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .sfa-dr-detail-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.78rem;
            min-width: 500px;
        }
        .sfa-dr-detail-table thead th {
            padding: 8px 14px;
            text-align: left;
            font-size: 0.66rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-secondary);
            background: var(--surface);
            border-bottom: 1px solid var(--card-border);
        }
        .sfa-dr-detail-th-num { width: 36px; text-align: center !important; }
        .sfa-dr-detail-th-name { }
        .sfa-dr-detail-th-dob { width: 80px; text-align: center !important; }
        .sfa-dr-detail-th-gender { width: 36px; text-align: center !important; }
        .sfa-dr-detail-th-parent { width: 150px; }
        .sfa-dr-detail-th-phone { width: 110px; }
        .sfa-dr-detail-th-status { width: 60px; text-align: center !important; }

        .sfa-dr-detail-table tbody td {
            padding: 7px 14px;
            border-bottom: 1px solid color-mix(in srgb, var(--card-border) 40%, transparent);
            color: var(--text-primary);
        }
        .sfa-dr-detail-row:nth-child(even) {
            background: color-mix(in srgb, var(--surface) 50%, var(--card-bg));
        }
        .sfa-dr-detail-td-num { text-align: center; color: var(--text-tertiary); font-size: 0.72rem; }
        .sfa-dr-detail-td-name { font-weight: 500; }
        .sfa-dr-detail-td-dob { text-align: center; font-variant-numeric: tabular-nums; color: var(--text-secondary); font-size: 0.74rem; }
        .sfa-dr-detail-td-gender { text-align: center; font-weight: 600; font-size: 0.74rem; }
        .sfa-dr-detail-td-parent { font-size: 0.74rem; color: var(--text-secondary); }
        .sfa-dr-detail-td-phone { font-variant-numeric: tabular-nums; color: var(--text-secondary); font-size: 0.74rem; }
        .sfa-dr-detail-td-status { text-align: center; }

        /* Status badges for detail view */
        .sfa-dr-status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }
        .sfa-dr-status-badge--present { background: #ecfdf5; color: #065f46; }
        .sfa-dr-status-badge--absent { background: #fef2f2; color: #991b1b; }
        .sfa-dr-status-badge--sick { background: #eff6ff; color: #1e40af; }
        .sfa-dr-status-badge--late { background: #fffbeb; color: #92400e; }
        .sfa-dr-status-badge--excused { background: #f5f3ff; color: #5b21b6; }
        .sfa-dr-status-badge--not_marked { background: #f3f4f6; color: #6b7280; }
        .dark .sfa-dr-status-badge--present { background: rgba(5,150,105,0.15); color: #6ee7b7; }
        .dark .sfa-dr-status-badge--absent { background: rgba(220,38,38,0.15); color: #fca5a5; }
        .dark .sfa-dr-status-badge--sick { background: rgba(37,99,235,0.15); color: #93c5fd; }
        .dark .sfa-dr-status-badge--late { background: rgba(217,119,6,0.15); color: #fbbf24; }
        .dark .sfa-dr-status-badge--excused { background: rgba(124,58,237,0.15); color: #c4b5fd; }
        .dark .sfa-dr-status-badge--not_marked { background: rgba(107,114,128,0.15); color: #9ca3af; }

        /* Summary line */
        .sfa-dr-detail-summary {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            padding: 10px 18px;
            background: var(--surface);
            border-top: 1px solid var(--card-border);
        }
        .sfa-dr-detail-summary-item {
            font-size: 0.74rem;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 4px;
        }
        .sfa-dr-detail-summary--present { color: #065f46; background: #ecfdf5; }
        .sfa-dr-detail-summary--absent { color: #991b1b; background: #fef2f2; }
        .sfa-dr-detail-summary--sick { color: #1e40af; background: #eff6ff; }
        .sfa-dr-detail-summary--late { color: #92400e; background: #fffbeb; }
        .sfa-dr-detail-summary--excused { color: #5b21b6; background: #f5f3ff; }
        .sfa-dr-detail-summary--not-marked { color: #6b7280; background: #f3f4f6; }
        .dark .sfa-dr-detail-summary--present { color: #6ee7b7; background: rgba(5,150,105,0.15); }
        .dark .sfa-dr-detail-summary--absent { color: #fca5a5; background: rgba(220,38,38,0.15); }
        .dark .sfa-dr-detail-summary--sick { color: #93c5fd; background: rgba(37,99,235,0.15); }
        .dark .sfa-dr-detail-summary--late { color: #fbbf24; background: rgba(217,119,6,0.15); }
        .dark .sfa-dr-detail-summary--excused { color: #c4b5fd; background: rgba(124,58,237,0.15); }
        .dark .sfa-dr-detail-summary--not-marked { color: #9ca3af; background: rgba(107,114,128,0.15); }
    </style>
</x-filament-widgets::widget>
