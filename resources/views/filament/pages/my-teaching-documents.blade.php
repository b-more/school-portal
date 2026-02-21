<x-filament-panels::page>
    @php
        $teacher = $this->getTeacher();
    @endphp

    @if(!$teacher)
        <div class="text-center py-16 bg-gray-50 dark:bg-gray-800 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600">
            <x-heroicon-o-user class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-500"/>
            <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">
                Teacher Profile Not Found
            </h3>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
                Your account is not linked to a teacher profile. Please contact the administrator.
            </p>
        </div>
    @else
        {{ $this->table }}
    @endif
</x-filament-panels::page>
