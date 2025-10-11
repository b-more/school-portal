<x-filament-panels::page>
    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow">
            <h2 class="text-lg font-semibold mb-4">Student Library Clearance</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                Check student library clearance status. Students are cleared when they have no active or overdue loans and no unpaid fines.
            </p>
        </div>

        {{ $this->table }}
    </div>
</x-filament-panels::page>
