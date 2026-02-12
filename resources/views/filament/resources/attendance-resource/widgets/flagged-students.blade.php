@php
    $flaggedStudents = $flaggedStudents ?? [];
    $flaggedCount = $flaggedCount ?? 0;
    $lookbackDays = $lookbackDays ?? 30;
@endphp

<x-filament-widgets::widget>
    <div class="sfa-flagged">

        {{-- Header --}}
        <div class="sfa-flagged-head" wire:click="toggleExpanded" style="cursor: pointer;">
            <div class="sfa-flagged-head-left">
                <div class="sfa-flagged-icon-wrap">
                    <x-heroicon-s-exclamation-triangle class="w-4 h-4" />
                </div>
                <h3 class="sfa-flagged-title">Flagged Students — Frequent Absences</h3>
                @if($flaggedCount > 0)
                    <span class="sfa-flagged-count-badge">{{ $flaggedCount }}</span>
                @endif
            </div>
            <div class="sfa-flagged-head-right">
                <span class="sfa-flagged-period">Last {{ $lookbackDays }} days</span>
                <x-heroicon-o-chevron-down class="w-4 h-4 sfa-flagged-chevron {{ $this->expanded ? 'sfa-flagged-chevron--open' : '' }}" />
            </div>
        </div>

        {{-- Body (collapsible) --}}
        @if($this->expanded || $flaggedCount > 0)
            <div class="sfa-flagged-body {{ !$this->expanded && $flaggedCount > 0 ? 'sfa-flagged-body--collapsed' : '' }}">
                @if($flaggedCount === 0)
                    <div class="sfa-flagged-empty">
                        <x-heroicon-o-check-circle class="w-8 h-8 opacity-30" />
                        <span>No attendance concerns in the last {{ $lookbackDays }} days</span>
                    </div>
                @else
                    <div class="sfa-flagged-table-wrap">
                        <table class="sfa-flagged-table">
                            <thead>
                                <tr>
                                    <th class="sfa-fl-th-name">Student</th>
                                    <th class="sfa-fl-th-class">Class</th>
                                    <th class="sfa-fl-th-num">Absent</th>
                                    <th class="sfa-fl-th-num">Sick</th>
                                    <th class="sfa-fl-th-num">Excused</th>
                                    <th class="sfa-fl-th-num">Total</th>
                                    <th class="sfa-fl-th-date">Last Absent</th>
                                    <th class="sfa-fl-th-num">Streak</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($flaggedStudents as $student)
                                    <tr class="sfa-fl-row sfa-fl-row--{{ $student['severity'] }}">
                                        <td class="sfa-fl-td-name">{{ $student['student_name'] }}</td>
                                        <td class="sfa-fl-td-class">{{ $student['class_name'] }}</td>
                                        <td class="sfa-fl-td-num sfa-fl-td--absent">{{ $student['absent_count'] }}</td>
                                        <td class="sfa-fl-td-num sfa-fl-td--sick">{{ $student['sick_count'] }}</td>
                                        <td class="sfa-fl-td-num sfa-fl-td--excused">{{ $student['excused_count'] }}</td>
                                        <td class="sfa-fl-td-num sfa-fl-td--total">{{ $student['total_flagged'] }}</td>
                                        <td class="sfa-fl-td-date">{{ $student['last_absent_date'] }}</td>
                                        <td class="sfa-fl-td-num">
                                            @if($student['streak'] >= 3)
                                                <span class="sfa-fl-streak-badge">{{ $student['streak'] }}d</span>
                                            @else
                                                {{ $student['streak'] ?: '-' }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endif

    </div>

    <style>
        .sfa-flagged {
            font-family: 'DM Sans', ui-sans-serif, system-ui, sans-serif;
            --navy: #1e3a5f;
            --red: #dc2626;
            --green: #059669;
            --amber: #d97706;
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
        .dark .sfa-flagged {
            --card-bg: #1f2937;
            --card-border: #374151;
            --text-primary: #f9fafb;
            --text-secondary: #9ca3af;
            --text-tertiary: #6b7280;
            --surface: #111827;
        }

        /* ---- HEADER ---- */
        .sfa-flagged-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 18px;
            border-bottom: 1px solid var(--card-border);
            gap: 12px;
            transition: background 0.15s;
        }
        .sfa-flagged-head:hover {
            background: color-mix(in srgb, var(--navy) 2%, var(--card-bg));
        }
        .dark .sfa-flagged-head:hover {
            background: rgba(255,255,255,0.02);
        }
        .sfa-flagged-head-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sfa-flagged-icon-wrap {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            background: #fef3c7;
            color: var(--amber);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .dark .sfa-flagged-icon-wrap {
            background: rgba(217,119,6,0.15);
            color: #fbbf24;
        }
        .sfa-flagged-title {
            font-size: 0.825rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
        }
        .sfa-flagged-count-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 22px;
            height: 22px;
            border-radius: 11px;
            background: var(--red);
            color: #ffffff;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 0 6px;
        }
        .sfa-flagged-head-right {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .sfa-flagged-period {
            font-size: 0.7rem;
            color: var(--text-tertiary);
            font-weight: 500;
        }
        .sfa-flagged-chevron {
            color: var(--text-tertiary);
            transition: transform 0.2s;
        }
        .sfa-flagged-chevron--open {
            transform: rotate(180deg);
        }

        /* ---- BODY ---- */
        .sfa-flagged-body--collapsed {
            display: none;
        }

        /* ---- EMPTY STATE ---- */
        .sfa-flagged-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 32px 16px;
            gap: 8px;
            color: var(--text-tertiary);
            font-size: 0.82rem;
        }

        /* ---- TABLE ---- */
        .sfa-flagged-table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .sfa-flagged-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8rem;
            min-width: 560px;
        }
        .sfa-flagged-table thead th {
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
        .sfa-fl-th-num, .sfa-fl-td-num { text-align: center !important; }
        .sfa-fl-th-date, .sfa-fl-td-date { text-align: center !important; white-space: nowrap; }

        .sfa-flagged-table tbody td {
            padding: 9px 14px;
            border-bottom: 1px solid color-mix(in srgb, var(--card-border) 50%, transparent);
            color: var(--text-primary);
            font-variant-numeric: tabular-nums;
        }
        .sfa-fl-td-name {
            font-weight: 600;
            white-space: nowrap;
        }
        .sfa-fl-td-class {
            color: var(--text-secondary);
            font-size: 0.78rem;
            white-space: nowrap;
        }
        .sfa-fl-td--absent { color: var(--red); font-weight: 700; }
        .sfa-fl-td--sick { color: #2563eb; font-weight: 600; }
        .sfa-fl-td--excused { color: #7c3aed; font-weight: 600; }
        .sfa-fl-td--total { font-weight: 800; color: var(--text-primary); }

        /* Row severity */
        .sfa-fl-row--red {
            background: color-mix(in srgb, var(--red) 4%, var(--card-bg));
            border-left: 3px solid var(--red);
        }
        .sfa-fl-row--orange {
            background: color-mix(in srgb, var(--amber) 4%, var(--card-bg));
            border-left: 3px solid var(--amber);
        }
        .dark .sfa-fl-row--red {
            background: rgba(220,38,38,0.06);
        }
        .dark .sfa-fl-row--orange {
            background: rgba(217,119,6,0.06);
        }
        .sfa-flagged-table tbody tr:hover {
            background: color-mix(in srgb, var(--navy) 4%, transparent) !important;
        }
        .dark .sfa-flagged-table tbody tr:hover {
            background: rgba(255,255,255,0.04) !important;
        }

        /* Streak badge */
        .sfa-fl-streak-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            background: #fee2e2;
            color: #991b1b;
            font-size: 0.72rem;
            font-weight: 700;
        }
        .dark .sfa-fl-streak-badge {
            background: rgba(220,38,38,0.15);
            color: #f87171;
        }
    </style>
</x-filament-widgets::widget>
