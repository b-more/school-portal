<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Form Section --}}
        <x-filament::section>
            <x-slot name="heading">
                Select Class, Subject & Term
            </x-slot>

            <form wire:submit.prevent="submitResults">
                {{ $this->form }}
            </form>
        </x-filament::section>

        {{-- Grading Scale Reference --}}
        @if($gradingScale)
            <x-filament::section collapsible collapsed>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-academic-cap class="w-5 h-5 text-primary-500" />
                        <span>Grading Scale: {{ $gradingScale->name }}</span>
                    </div>
                </x-slot>

                <div class="flex flex-wrap gap-3">
                    @foreach($gradingScale->items as $item)
                        <div class="flex items-center gap-2 px-3 py-2 bg-gray-100 dark:bg-gray-800 rounded-lg">
                            <span class="font-bold text-lg
                                @if($item->grade === 'A') text-green-600
                                @elseif($item->grade === 'B') text-blue-600
                                @elseif($item->grade === 'C') text-yellow-600
                                @elseif($item->grade === 'D') text-orange-600
                                @else text-red-600
                                @endif
                            ">{{ $item->grade }}</span>
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $item->min_marks }}-{{ $item->max_marks }}%
                            </span>
                            <span class="text-xs text-gray-500 dark:text-gray-500">({{ $item->remark }})</span>
                        </div>
                    @endforeach
                </div>
            </x-filament::section>
        @endif

        {{-- Results Entry Grid --}}
        @if(count($students) > 0)
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center justify-between w-full">
                        <span>Enter Results ({{ count($students) }} Students)</span>
                        <div class="flex gap-2">
                            <x-filament::button
                                wire:click="clearAllMarks"
                                color="gray"
                                size="sm"
                                outlined
                            >
                                <x-heroicon-o-trash class="w-4 h-4 mr-1" />
                                Clear All
                            </x-filament::button>
                        </div>
                    </div>
                </x-slot>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-8">
                                    #
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Student Name
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Student ID
                                </th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-32">
                                    Marks (0-100)
                                </th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-24">
                                    Grade
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Comment (Optional)
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($students as $index => $student)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                    <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                        {{ $index + 1 }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900 dark:text-white">
                                            {{ $student['name'] }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                        {{ $student['student_id_number'] ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <input
                                            type="number"
                                            min="0"
                                            max="100"
                                            step="0.01"
                                            wire:model.lazy="resultsData.{{ $student['id'] }}.marks"
                                            wire:change="updateMarks({{ $student['id'] }}, $event.target.value)"
                                            class="w-full px-3 py-2 text-center border border-gray-300 dark:border-gray-600 rounded-lg
                                                   bg-white dark:bg-gray-800 text-gray-900 dark:text-white
                                                   focus:ring-2 focus:ring-primary-500 focus:border-primary-500
                                                   @if(isset($resultsData[$student['id']]['marks']) && $resultsData[$student['id']]['marks'] !== '')
                                                       @if($resultsData[$student['id']]['marks'] >= 75) border-green-400 bg-green-50 dark:bg-green-900/20
                                                       @elseif($resultsData[$student['id']]['marks'] >= 50) border-yellow-400 bg-yellow-50 dark:bg-yellow-900/20
                                                       @elseif($resultsData[$student['id']]['marks'] >= 0) border-red-400 bg-red-50 dark:bg-red-900/20
                                                       @endif
                                                   @endif"
                                            placeholder="--"
                                        />
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center justify-center w-10 h-10 rounded-full font-bold text-lg
                                            @if(($resultsData[$student['id']]['grade'] ?? '') === 'A') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                            @elseif(($resultsData[$student['id']]['grade'] ?? '') === 'B') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                            @elseif(($resultsData[$student['id']]['grade'] ?? '') === 'C') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                            @elseif(($resultsData[$student['id']]['grade'] ?? '') === 'D') bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200
                                            @elseif(($resultsData[$student['id']]['grade'] ?? '') === 'E') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                            @elseif(($resultsData[$student['id']]['grade'] ?? '') === 'F') bg-red-200 text-red-900 dark:bg-red-800 dark:text-red-100
                                            @else bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400
                                            @endif
                                        ">
                                            {{ $resultsData[$student['id']]['grade'] ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <input
                                            type="text"
                                            wire:model.lazy="resultsData.{{ $student['id'] }}.comment"
                                            wire:change="updateComment({{ $student['id'] }}, $event.target.value)"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg
                                                   bg-white dark:bg-gray-800 text-gray-900 dark:text-white
                                                   focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                            placeholder="Optional comment..."
                                        />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Summary --}}
                <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4 text-center">
                        <div>
                            <div class="text-2xl font-bold text-gray-600 dark:text-gray-300">
                                {{ count($students) }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Total</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-green-600">
                                {{ collect($resultsData)->filter(fn($data) => ($data['grade'] ?? '') === 'A')->count() }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Grade A</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-blue-600">
                                {{ collect($resultsData)->filter(fn($data) => ($data['grade'] ?? '') === 'B')->count() }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Grade B</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-yellow-600">
                                {{ collect($resultsData)->filter(fn($data) => ($data['grade'] ?? '') === 'C')->count() }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Grade C</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-orange-600">
                                {{ collect($resultsData)->filter(fn($data) => ($data['grade'] ?? '') === 'D')->count() }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Grade D</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-red-500">
                                {{ collect($resultsData)->filter(fn($data) => in_array(($data['grade'] ?? ''), ['E', 'F']))->count() }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Grade E/F</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-gray-400">
                                {{ collect($resultsData)->filter(fn($data) => ($data['marks'] ?? '') === '')->count() }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Pending</div>
                        </div>
                    </div>
                </div>

                {{-- Submit Button --}}
                <div class="mt-6 flex justify-end gap-3">
                    <x-filament::button
                        wire:click="submitResults"
                        size="lg"
                        color="primary"
                    >
                        <x-heroicon-o-check class="w-5 h-5 mr-2" />
                        Save All Results
                    </x-filament::button>
                </div>
            </x-filament::section>
        @else
            <x-filament::section>
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No students loaded</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Please select a class, subject, and term to start entering results
                    </p>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
