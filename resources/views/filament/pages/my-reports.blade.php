<x-filament-panels::page>
    @php
        $studentStats = $this->getStudentStats();
        $homeworkStats = $this->getHomeworkStats();
        $attendanceStats = $this->getAttendanceStats();
        $performanceByClass = $this->getPerformanceByClass();
    @endphp

    <!-- Student Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <x-filament::section>
            <div class="text-center">
                <div class="w-16 h-16 mx-auto rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center mb-3">
                    <x-heroicon-o-user-group class="w-8 h-8 text-white" />
                </div>
                <div class="text-4xl font-bold text-gray-900 dark:text-gray-100">{{ $studentStats['total'] }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Total Students</div>
                <div class="flex justify-center gap-4 mt-3 text-xs">
                    <span class="text-blue-600 dark:text-blue-400">
                        <strong>{{ $studentStats['boys'] }}</strong> Boys
                    </span>
                    <span class="text-pink-600 dark:text-pink-400">
                        <strong>{{ $studentStats['girls'] }}</strong> Girls
                    </span>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-center">
                <div class="w-16 h-16 mx-auto rounded-full bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center mb-3">
                    <x-heroicon-o-document-text class="w-8 h-8 text-white" />
                </div>
                <div class="text-4xl font-bold text-gray-900 dark:text-gray-100">{{ $homeworkStats['total'] }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Total Homework</div>
                <div class="flex justify-center gap-4 mt-3 text-xs">
                    <span class="text-orange-600 dark:text-orange-400">
                        <strong>{{ $homeworkStats['pending'] }}</strong> Pending
                    </span>
                    <span class="text-green-600 dark:text-green-400">
                        <strong>{{ $homeworkStats['graded'] }}</strong> Graded
                    </span>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-center">
                <div class="w-16 h-16 mx-auto rounded-full bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center mb-3">
                    <x-heroicon-o-check-circle class="w-8 h-8 text-white" />
                </div>
                <div class="text-4xl font-bold text-gray-900 dark:text-gray-100">{{ $attendanceStats['today'] }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Attendance Today</div>
                <div class="flex justify-center gap-3 mt-3 text-xs">
                    <span class="text-gray-600 dark:text-gray-400">
                        <strong>{{ $attendanceStats['week'] }}</strong> Week
                    </span>
                    <span class="text-gray-600 dark:text-gray-400">
                        <strong>{{ $attendanceStats['month'] }}</strong> Month
                    </span>
                </div>
            </div>
        </x-filament::section>
    </div>

    <!-- Class Performance -->
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-heroicon-o-chart-bar class="w-5 h-5 text-blue-600" />
                <span>Class Performance Overview</span>
            </div>
        </x-slot>

        @if($performanceByClass->isEmpty())
            <div class="text-center py-12 bg-gray-50 dark:bg-gray-800 rounded-lg border border-dashed border-gray-300 dark:border-gray-600">
                <x-heroicon-o-chart-bar class="w-12 h-12 mx-auto text-gray-400 mb-3" />
                <p class="text-gray-500 dark:text-gray-400">No performance data available yet</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Class</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Students</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Average Score</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Performance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($performanceByClass as $class)
                            <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ $class->grade_name }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Section {{ $class->section_name }}</div>
                                </td>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $class->student_count }}</td>
                                <td class="px-4 py-3">
                                    <span class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ number_format($class->avg_marks, 1) }}%</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                            <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-2 rounded-full" style="width: {{ min($class->avg_marks, 100) }}%"></div>
                                        </div>
                                        @if($class->avg_marks >= 75)
                                            <span class="text-xs font-semibold text-green-600 dark:text-green-400">Excellent</span>
                                        @elseif($class->avg_marks >= 60)
                                            <span class="text-xs font-semibold text-blue-600 dark:text-blue-400">Good</span>
                                        @elseif($class->avg_marks >= 50)
                                            <span class="text-xs font-semibold text-yellow-600 dark:text-yellow-400">Average</span>
                                        @else
                                            <span class="text-xs font-semibold text-red-600 dark:text-red-400">Needs Improvement</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>
</x-filament-panels::page>
