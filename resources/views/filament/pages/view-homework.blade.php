<!-- resources/views/filament/pages/view-homework.blade.php -->
<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="bg-white rounded-lg shadow p-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-2">
                {{ auth()->user()->hasRole('parent') ? 'Your Children\'s Homework' : 'My Homework' }}
            </h1>
            <p class="text-gray-600">
                View and download homework assignments
            </p>
        </div>

        <!-- Homework Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
