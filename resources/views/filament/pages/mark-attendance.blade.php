<x-filament-panels::page>
    <div class="space-y-4">
        {{-- Header with Form --}}
        <x-filament::section>
            <div class="flex flex-col md:flex-row md:items-end gap-4">
                <div class="flex-1">
                    {{ $this->form }}
                </div>
                @if(count($students) > 0)
                    <div class="flex gap-2">
                        <x-filament::button
                            wire:click="markAllPresent"
                            color="success"
                            size="sm"
                        >
                            All Present
                        </x-filament::button>
                        <x-filament::button
                            wire:click="submitAttendance"
                            color="primary"
                        >
                            Save
                        </x-filament::button>
                    </div>
                @endif
            </div>
        </x-filament::section>

        {{-- Already Marked Notice --}}
        @if($attendanceAlreadyMarked && count($students) > 0)
            <div class="flex items-center gap-2 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                <x-heroicon-o-information-circle class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                <span class="text-sm text-blue-700 dark:text-blue-300">
                    Attendance already marked for this date. Changes will update existing records.
                </span>
            </div>
        @endif

        {{-- Students Grid --}}
        @if(count($students) > 0)
            {{-- Summary Bar --}}
            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <div class="flex items-center gap-6 text-sm">
                    <span class="font-medium text-gray-700 dark:text-gray-300">
                        {{ count($students) }} Students
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="w-3 h-3 bg-green-500 rounded-full"></span>
                        <span class="text-green-700 dark:text-green-400 font-semibold">
                            {{ collect($attendanceData)->filter(fn($s) => $s === 'present')->count() }}
                        </span>
                        <span class="text-gray-500">Present</span>
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="w-3 h-3 bg-red-500 rounded-full"></span>
                        <span class="text-red-700 dark:text-red-400 font-semibold">
                            {{ collect($attendanceData)->filter(fn($s) => $s === 'absent')->count() }}
                        </span>
                        <span class="text-gray-500">Absent</span>
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="w-3 h-3 bg-blue-500 rounded-full"></span>
                        <span class="text-blue-700 dark:text-blue-400 font-semibold">
                            {{ collect($attendanceData)->filter(fn($s) => $s === 'sick')->count() }}
                        </span>
                        <span class="text-gray-500">Sick</span>
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="w-3 h-3 bg-orange-500 rounded-full"></span>
                        <span class="text-orange-700 dark:text-orange-400 font-semibold">
                            {{ collect($attendanceData)->filter(fn($s) => $s === 'late')->count() }}
                        </span>
                        <span class="text-gray-500">Late</span>
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="w-3 h-3 bg-purple-500 rounded-full"></span>
                        <span class="text-purple-700 dark:text-purple-400 font-semibold">
                            {{ collect($attendanceData)->filter(fn($s) => $s === 'excused')->count() }}
                        </span>
                        <span class="text-gray-500">Excused</span>
                    </span>
                </div>
                <div class="text-xs text-gray-500">
                    Tap student to toggle: Present > Absent > Sick > Late > Excused
                </div>
            </div>

            {{-- Compact Student Grid --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-2">
                @foreach($students as $index => $student)
                    @php
                        $status = $attendanceData[$student['id']] ?? 'present';
                        $statusColors = [
                            'present' => 'bg-green-100 dark:bg-green-900/30 border-green-400 dark:border-green-700 hover:bg-green-200',
                            'absent' => 'bg-red-100 dark:bg-red-900/30 border-red-400 dark:border-red-700 hover:bg-red-200',
                            'sick' => 'bg-blue-100 dark:bg-blue-900/30 border-blue-400 dark:border-blue-700 hover:bg-blue-200',
                            'late' => 'bg-orange-100 dark:bg-orange-900/30 border-orange-400 dark:border-orange-700 hover:bg-orange-200',
                            'excused' => 'bg-purple-100 dark:bg-purple-900/30 border-purple-400 dark:border-purple-700 hover:bg-purple-200',
                        ];
                        $textColors = [
                            'present' => 'text-green-800 dark:text-green-200',
                            'absent' => 'text-red-800 dark:text-red-200',
                            'sick' => 'text-blue-800 dark:text-blue-200',
                            'late' => 'text-orange-800 dark:text-orange-200',
                            'excused' => 'text-purple-800 dark:text-purple-200',
                        ];
                        $statusIcons = [
                            'present' => 'M5 13l4 4L19 7',
                            'absent' => 'M6 18L18 6M6 6l12 12',
                            'sick' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z',
                            'late' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                            'excused' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                        ];
                    @endphp
                    <button
                        type="button"
                        wire:click="toggleStatus({{ $student['id'] }})"
                        class="relative flex flex-col items-center p-3 rounded-lg border-2 transition-all cursor-pointer
                            {{ $statusColors[$status] }}"
                    >
                        {{-- Number Badge --}}
                        <span class="absolute top-1 left-1 text-xs font-medium text-gray-400">
                            {{ $index + 1 }}
                        </span>

                        {{-- Status Icon --}}
                        <div class="w-8 h-8 rounded-full flex items-center justify-center mb-1
                            @if($status === 'present') bg-green-500
                            @elseif($status === 'absent') bg-red-500
                            @elseif($status === 'sick') bg-blue-500
                            @elseif($status === 'late') bg-orange-500
                            @elseif($status === 'excused') bg-purple-500
                            @else bg-gray-500
                            @endif
                        ">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="{{ $statusIcons[$status] }}" />
                            </svg>
                        </div>

                        {{-- Student Name --}}
                        <span class="text-xs font-medium text-center leading-tight {{ $textColors[$status] }} line-clamp-2">
                            {{ Str::limit($student['name'], 18) }}
                        </span>

                        {{-- Status Label --}}
                        <span class="text-[10px] uppercase tracking-wide mt-1 {{ $textColors[$status] }} opacity-75">
                            {{ $status }}
                        </span>
                    </button>
                @endforeach
            </div>

            {{-- Bottom Save Button (Mobile) --}}
            <div class="md:hidden sticky bottom-4 flex justify-center">
                <x-filament::button
                    wire:click="submitAttendance"
                    color="primary"
                    size="lg"
                    class="shadow-lg"
                >
                    <x-heroicon-o-check class="w-5 h-5 mr-2" />
                    Save Attendance
                </x-filament::button>
            </div>
        @else
            {{-- Empty State --}}
            <x-filament::section>
                <div class="text-center py-12">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                        <x-heroicon-o-user-group class="w-8 h-8 text-gray-400" />
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Select a Class</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Choose a class above to start marking attendance
                    </p>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
