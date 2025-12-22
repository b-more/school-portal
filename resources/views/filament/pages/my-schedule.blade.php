<x-filament-panels::page>
    @php
        $teacher = $this->getTeacher();
        $classes = $this->getMyClasses();
        $subjects = $this->getMySubjects();
        $periods = $this->getPeriods();
        $days = $this->getDays();
        $timetableData = $this->getTimetableData();
        $teachingLoad = $this->getTeachingLoad();
        $printUrl = $this->getPrintUrl();
        $academicYear = $this->getAcademicYear();
    @endphp

    @if(!$teacher)
        <div class="text-center py-16 bg-gray-50 dark:bg-gray-800 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600">
            <x-heroicon-o-user class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-500"/>
            <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">
                Teacher Profile Not Found
            </h3>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
                Your user account is not linked to a teacher profile. Please contact the administrator.
            </p>
        </div>
    @else
        <!-- Teacher Info Header -->
        <div class="mb-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0">
                        <div class="w-16 h-16 rounded-full bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center text-white text-2xl font-bold shadow-lg">
                            {{ strtoupper(substr($teacher->name, 0, 1)) }}
                        </div>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $teacher->name }}</h2>
                        <p class="text-gray-600 dark:text-gray-400">{{ $teacher->employee_id ?? 'N/A' }} | {{ $teacher->qualification ?? 'Teacher' }}</p>
                        <p class="text-sm text-primary-600 dark:text-primary-400 mt-1">
                            Academic Year: {{ $academicYear?->name ?? 'N/A' }}
                        </p>
                    </div>
                </div>
                @if($printUrl)
                    <x-filament::button
                        color="success"
                        tag="a"
                        href="{{ $printUrl }}"
                        target="_blank"
                        icon="heroicon-o-printer"
                    >
                        Print Schedule
                    </x-filament::button>
                @endif
            </div>
        </div>

        <!-- Teaching Load Summary -->
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

        <!-- Weekly Timetable Grid -->
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-calendar-days class="w-6 h-6 text-primary-600" />
                    <span>My Weekly Schedule</span>
                </div>
            </x-slot>

            @if($periods->isEmpty())
                <div class="text-center py-12 bg-gray-50 dark:bg-gray-800 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600">
                    <x-heroicon-o-calendar class="w-12 h-12 mx-auto text-gray-400 mb-3" />
                    <p class="text-gray-500 dark:text-gray-400">No timetable periods have been set up yet.</p>
                    <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Contact the administrator to set up timetable periods.</p>
                </div>
            @else
                <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
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
                                                {{-- Break period --}}
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
                                                {{-- Lesson period --}}
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
        </x-filament::section>

        <!-- Daily Load Breakdown -->
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

        <!-- My Classes Section (Collapsible) -->
        <x-filament::section class="mt-6" collapsible collapsed>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-academic-cap class="w-6 h-6 text-blue-600" />
                    <span>My Classes ({{ $classes->count() }})</span>
                </div>
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($classes as $classSectionId => $teachings)
                    @php
                        $classSection = $teachings->first()->classSection;
                    @endphp
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-800 border-2 border-blue-200 dark:border-blue-700 rounded-lg p-4 hover:shadow-lg transition-shadow">
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                    {{ $classSection->grade->name ?? 'N/A' }} - {{ $classSection->name }}
                                </h3>
                            </div>
                            @if($teacher?->is_class_teacher && $teacher->class_section_id === $classSection->id)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    Class Teacher
                                </span>
                            @endif
                        </div>

                        <div class="space-y-2">
                            <div class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Subjects:</div>
                            @foreach($teachings as $teaching)
                                <div class="flex items-center gap-2 text-sm">
                                    <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                                    <span class="text-gray-900 dark:text-gray-100 font-medium">{{ $teaching->subject->name }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="col-span-full">
                        <div class="text-center py-8 bg-gray-50 dark:bg-gray-800 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600">
                            <x-heroicon-o-academic-cap class="w-12 h-12 mx-auto text-gray-400 mb-3" />
                            <p class="text-gray-500 dark:text-gray-400">No classes assigned yet</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
