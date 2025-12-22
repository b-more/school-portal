<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Filter Form --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Report Filters</h2>
            <form wire:submit.prevent="submit">
                {{ $this->form }}
            </form>
        </div>

        {{-- Summary Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            @php
                $query = $this->getTableQuery();
                $totalRecords = $query->count();
                $presentCount = $query->where('status', 'present')->count();
                $absentCount = $query->where('status', 'absent')->count();
                $lateCount = $query->where('status', 'late')->count();
                $excusedCount = $query->where('status', 'excused')->count();
                $attendanceRate = $totalRecords > 0 ? round(($presentCount / $totalRecords) * 100, 1) : 0;
            @endphp

            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                <div class="text-sm text-blue-600 dark:text-blue-400 font-medium">Total Records</div>
                <div class="text-2xl font-bold text-blue-900 dark:text-blue-100">{{ number_format($totalRecords) }}</div>
            </div>

            <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                <div class="text-sm text-green-600 dark:text-green-400 font-medium">Present</div>
                <div class="text-2xl font-bold text-green-900 dark:text-green-100">{{ number_format($presentCount) }}</div>
                <div class="text-xs text-green-600 dark:text-green-400">{{ $attendanceRate }}% rate</div>
            </div>

            <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4">
                <div class="text-sm text-red-600 dark:text-red-400 font-medium">Absent</div>
                <div class="text-2xl font-bold text-red-900 dark:text-red-100">{{ number_format($absentCount) }}</div>
            </div>

            <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-4">
                <div class="text-sm text-yellow-600 dark:text-yellow-400 font-medium">Late</div>
                <div class="text-2xl font-bold text-yellow-900 dark:text-yellow-100">{{ number_format($lateCount) }}</div>
            </div>

            <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4">
                <div class="text-sm text-purple-600 dark:text-purple-400 font-medium">Excused</div>
                <div class="text-2xl font-bold text-purple-900 dark:text-purple-100">{{ number_format($excusedCount) }}</div>
            </div>
        </div>

        {{-- Results Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
