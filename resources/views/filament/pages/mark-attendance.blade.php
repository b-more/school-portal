<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Form Section --}}
        <x-filament::section>
            <x-slot name="heading">
                Select Class & Date
            </x-slot>

            <form wire:submit.prevent="submitAttendance">
                {{ $this->form }}
            </form>
        </x-filament::section>

        {{-- Students Grid --}}
        @if(count($students) > 0)
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center justify-between w-full">
                        <span>Mark Attendance ({{ count($students) }} Students)</span>
                        <div class="flex gap-2">
                            <x-filament::button
                                wire:click="markAllPresent"
                                color="success"
                                size="sm"
                                outlined
                            >
                                <x-heroicon-o-check-circle class="w-4 h-4 mr-1" />
                                All Present
                            </x-filament::button>

                            <x-filament::button
                                wire:click="markAllAbsent"
                                color="danger"
                                size="sm"
                                outlined
                            >
                                <x-heroicon-o-x-circle class="w-4 h-4 mr-1" />
                                All Absent
                            </x-filament::button>
                        </div>
                    </div>
                </x-slot>

                <div class="space-y-3">
                    @foreach($students as $student)
                        <div class="flex items-center justify-between p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-primary-500 transition">
                            {{-- Student Info --}}
                            <div class="flex-1">
                                <div class="font-medium text-gray-900 dark:text-white">
                                    {{ $student['name'] }}
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    Student ID: {{ $student['id'] }}
                                </div>
                            </div>

                            {{-- Status Buttons --}}
                            <div class="flex gap-2">
                                {{-- Present Button --}}
                                <button
                                    type="button"
                                    wire:click="setStatus({{ $student['id'] }}, 'present')"
                                    class="flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-all
                                        @if(($attendanceData[$student['id']] ?? 'present') === 'present')
                                            bg-green-500 text-white shadow-lg scale-105
                                        @else
                                            bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-green-100 dark:hover:bg-green-900
                                        @endif
                                    "
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="hidden sm:inline">Present</span>
                                </button>

                                {{-- Absent Button --}}
                                <button
                                    type="button"
                                    wire:click="setStatus({{ $student['id'] }}, 'absent')"
                                    class="flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-all
                                        @if(($attendanceData[$student['id']] ?? 'present') === 'absent')
                                            bg-red-500 text-white shadow-lg scale-105
                                        @else
                                            bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-red-100 dark:hover:bg-red-900
                                        @endif
                                    "
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    <span class="hidden sm:inline">Absent</span>
                                </button>

                                {{-- Late Button --}}
                                <button
                                    type="button"
                                    wire:click="setStatus({{ $student['id'] }}, 'late')"
                                    class="flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-all
                                        @if(($attendanceData[$student['id']] ?? 'present') === 'late')
                                            bg-orange-500 text-white shadow-lg scale-105
                                        @else
                                            bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-orange-100 dark:hover:bg-orange-900
                                        @endif
                                    "
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="hidden sm:inline">Late</span>
                                </button>

                                {{-- Excused Button --}}
                                <button
                                    type="button"
                                    wire:click="setStatus({{ $student['id'] }}, 'excused')"
                                    class="flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-all
                                        @if(($attendanceData[$student['id']] ?? 'present') === 'excused')
                                            bg-blue-500 text-white shadow-lg scale-105
                                        @else
                                            bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-blue-100 dark:hover:bg-blue-900
                                        @endif
                                    "
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="hidden sm:inline">Excused</span>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Summary --}}
                <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                    <div class="grid grid-cols-4 gap-4 text-center">
                        <div>
                            <div class="text-2xl font-bold text-green-600">
                                {{ collect($attendanceData)->filter(fn($status) => $status === 'present')->count() }}
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Present</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-red-600">
                                {{ collect($attendanceData)->filter(fn($status) => $status === 'absent')->count() }}
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Absent</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-orange-600">
                                {{ collect($attendanceData)->filter(fn($status) => $status === 'late')->count() }}
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Late</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-blue-600">
                                {{ collect($attendanceData)->filter(fn($status) => $status === 'excused')->count() }}
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Excused</div>
                        </div>
                    </div>
                </div>

                {{-- Submit Button --}}
                <div class="mt-6 flex justify-end">
                    <x-filament::button
                        wire:click="submitAttendance"
                        size="lg"
                        color="primary"
                    >
                        <x-heroicon-o-check class="w-5 h-5 mr-2" />
                        Save Attendance
                    </x-filament::button>
                </div>
            </x-filament::section>
        @else
            <x-filament::section>
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No students loaded</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Please select a class to start marking attendance</p>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
