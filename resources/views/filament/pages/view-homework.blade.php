<x-filament::page>
    <div>
        @if(!$selectedChildId || empty($children))
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <h2 class="text-lg font-semibold mb-4">Welcome to the Homework Portal</h2>

                @if(empty($children))
                    <p class="text-gray-600">You don't have any children registered in the system.</p>
                @else
                    <p class="text-gray-600">Please select a child to view their homework assignments.</p>
                    <div class="mt-4">
                        <x-filament::button color="primary" x-on:click="$dispatch('open-modal', { id: 'select_child' })">
                            Select Child
                        </x-filament::button>
                    </div>
                @endif
            </div>
        @else
            <div class="mb-6">
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold">
                                Viewing Homework for: {{ \App\Models\Student::find($selectedChildId)->name ?? 'Unknown Student' }}
                            </h2>
                            <p class="text-sm text-gray-500">
                                Grade: {{ \App\Models\Student::find($selectedChildId)->grade ?? 'N/A' }}
                            </p>
                        </div>
                        <div>
                            <x-filament::button color="secondary" x-on:click="$dispatch('open-modal', { id: 'select_child' })">
                                Change Child
                            </x-filament::button>
                        </div>
                    </div>
                </div>
            </div>

            {{ $this->table }}
        @endif
    </div>
</x-filament::page>
