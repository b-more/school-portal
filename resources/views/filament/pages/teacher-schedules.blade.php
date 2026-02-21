<x-filament-panels::page>
    <form wire:submit.prevent>
        {{ $this->form }}
    </form>

    @if($selectedTeacher && $selectedAcademicYear)
        @php
            $periods = $this->getPeriods();
            $days = $this->getDays();
            $timetableData = $this->getTimetableData();
            $teachingLoad = $this->getTeachingLoad();
            $printUrl = $this->getPrintUrl();
            $teacherName = $this->getSelectedTeacherName();
        @endphp

        <div class="mt-6">
            {{-- Header --}}
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Schedule: {{ $teacherName }}
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        View the teacher's weekly timetable
                    </p>
                </div>
                @if($printUrl)
                    <x-filament::button
                        color="success"
                        tag="a"
                        href="{{ $printUrl }}"
                        target="_blank"
                        icon="heroicon-o-printer"
                        size="sm"
                    >
                        Print Schedule
                    </x-filament::button>
                @endif
            </div>

            {{-- Teaching Load Summary --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 text-center">
                    <div class="text-3xl font-bold text-primary-600 dark:text-primary-400">{{ $teachingLoad['total_periods'] }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Periods/Week</div>
                </div>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 text-center">
                    <div class="text-3xl font-bold text-success-600 dark:text-success-400">{{ $teachingLoad['classes_taught'] }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Classes</div>
                </div>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 text-center">
                    <div class="text-3xl font-bold text-warning-600 dark:text-warning-400">{{ $teachingLoad['subjects_taught'] }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Subjects</div>
                </div>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 text-center">
                    <div class="text-3xl font-bold text-info-600 dark:text-info-400">{{ $periods->where('type', 'lesson')->count() }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Max Periods/Day</div>
                </div>
            </div>

            {{-- Timetable Grid --}}
            @if($periods->isEmpty())
                <div class="text-center py-12 bg-gray-50 dark:bg-gray-800 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600">
                    <x-heroicon-o-calendar class="w-12 h-12 mx-auto text-gray-400 mb-3" />
                    <p class="text-gray-500 dark:text-gray-400">No timetable periods have been set up for this academic year.</p>
                </div>
            @else
                <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-primary-600 dark:bg-primary-700">
                                <th class="border-r border-primary-500 px-3 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider w-32">
                                    Period
                                </th>
                                <th class="border-r border-primary-500 px-3 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider w-20">
                                    Time
                                </th>
                                @foreach($days as $day)
                                    <th class="border-r border-primary-500 last:border-r-0 px-3 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider">
                                        {{ $day }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($periods as $period)
                                <tr class="{{ $period->isBreak() ? 'bg-amber-50 dark:bg-amber-900/20' : '' }}">
                                    {{-- Period Name --}}
                                    <td class="border-r border-gray-200 dark:border-gray-700 px-3 py-2">
                                        <div class="font-medium text-gray-900 dark:text-white text-sm">
                                            {{ $period->name }}
                                        </div>
                                        @if($period->short_name)
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                ({{ $period->short_name }})
                                            </div>
                                        @endif
                                        @if($period->isBreak())
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-800/30 dark:text-amber-300 mt-1">
                                                Break
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Time --}}
                                    <td class="border-r border-gray-200 dark:border-gray-700 px-2 py-2 text-center">
                                        <div class="text-xs text-gray-600 dark:text-gray-400 whitespace-nowrap">
                                            {{ \Carbon\Carbon::parse($period->start_time)->format('H:i') }}
                                        </div>
                                        <div class="text-xs text-gray-400 dark:text-gray-500">
                                            {{ \Carbon\Carbon::parse($period->end_time)->format('H:i') }}
                                        </div>
                                    </td>

                                    {{-- Day Cells --}}
                                    @foreach($days as $day)
                                        <td class="border-r border-gray-200 dark:border-gray-700 last:border-r-0 px-1 py-1 text-center">
                                            @if($period->isBreak())
                                                <div class="h-16 flex items-center justify-center">
                                                    <span class="text-amber-600 dark:text-amber-400 text-xs font-medium">
                                                        @switch($period->type)
                                                            @case('assembly')
                                                                Assembly
                                                                @break
                                                            @case('tea_break')
                                                                Tea Break
                                                                @break
                                                            @case('lunch_break')
                                                                Lunch
                                                                @break
                                                            @default
                                                                Break
                                                        @endswitch
                                                    </span>
                                                </div>
                                            @else
                                                @php
                                                    $entry = $timetableData[$period->id][$day] ?? null;
                                                @endphp
                                                <div class="h-16 p-1.5 rounded-md {{ $entry
                                                    ? 'bg-primary-50 dark:bg-primary-900/30 border border-primary-200 dark:border-primary-700'
                                                    : 'bg-gray-50 dark:bg-gray-700/50' }}">
                                                    @if($entry)
                                                        <div class="h-full flex flex-col justify-center">
                                                            <div class="text-xs font-semibold text-primary-700 dark:text-primary-300 truncate">
                                                                {{ $entry->classSection?->grade?->name ?? '' }} {{ $entry->classSection?->name ?? '' }}
                                                            </div>
                                                            <div class="text-[10px] text-gray-600 dark:text-gray-400 truncate mt-0.5">
                                                                {{ $entry->subject?->name ?? '-' }}
                                                            </div>
                                                            @if($entry->room)
                                                                <div class="text-[9px] text-gray-500 dark:text-gray-500 truncate">
                                                                    {{ $entry->room }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @else
                                                        <div class="h-full flex items-center justify-center">
                                                            <span class="text-gray-300 dark:text-gray-600 text-xs">-</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Legend --}}
                <div class="mt-4 flex flex-wrap gap-4 text-xs text-gray-600 dark:text-gray-400">
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded bg-primary-50 dark:bg-primary-900/30 border border-primary-200 dark:border-primary-700"></div>
                        <span>Teaching</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded bg-gray-50 dark:bg-gray-700/50"></div>
                        <span>Free Period</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded bg-amber-50 dark:bg-amber-900/20"></div>
                        <span>Break</span>
                    </div>
                </div>
            @endif

            {{-- Daily Load Breakdown --}}
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mt-6">
                @foreach($days as $day)
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4">
                        <div class="text-center">
                            <div class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">{{ $day }}</div>
                            <div class="text-2xl font-bold {{ ($teachingLoad['periods_per_day'][$day] ?? 0) > 0 ? 'text-primary-600 dark:text-primary-400' : 'text-gray-400' }}">
                                {{ $teachingLoad['periods_per_day'][$day] ?? 0 }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">periods</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        {{-- Empty State --}}
        <div class="mt-6 text-center py-16 bg-gray-50 dark:bg-gray-800 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600">
            <x-heroicon-o-user-group class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-500"/>
            <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">
                No Teacher Selected
            </h3>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
                Select an academic year and teacher above to view their schedule.
            </p>
        </div>
    @endif
</x-filament-panels::page>
