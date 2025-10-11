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
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @php
                $query = $this->getTableQuery();
                $totalFees = $query->get()->sum(fn($fee) => $fee->feeStructure->total_fee ?? 0);
                $totalPaid = $query->sum('amount_paid');
                $totalBalance = $query->sum('balance');
                $totalStudents = $query->count();
            @endphp

            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                <div class="text-sm text-blue-600 dark:text-blue-400 font-medium">Total Students</div>
                <div class="text-2xl font-bold text-blue-900 dark:text-blue-100">{{ number_format($totalStudents) }}</div>
            </div>

            <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                <div class="text-sm text-green-600 dark:text-green-400 font-medium">Total Fees</div>
                <div class="text-2xl font-bold text-green-900 dark:text-green-100">ZMW {{ number_format($totalFees, 2) }}</div>
            </div>

            <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-4">
                <div class="text-sm text-yellow-600 dark:text-yellow-400 font-medium">Amount Paid</div>
                <div class="text-2xl font-bold text-yellow-900 dark:text-yellow-100">ZMW {{ number_format($totalPaid, 2) }}</div>
            </div>

            <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4">
                <div class="text-sm text-red-600 dark:text-red-400 font-medium">Outstanding Balance</div>
                <div class="text-2xl font-bold text-red-900 dark:text-red-100">ZMW {{ number_format($totalBalance, 2) }}</div>
            </div>
        </div>

        {{-- Results Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
