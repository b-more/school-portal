<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Form Section --}}
        <x-filament::section>
            <x-slot name="heading">
                Select Class & Term
            </x-slot>

            {{ $this->form }}
        </x-filament::section>

        {{-- Students Grid --}}
        @if(count($students) > 0)
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center justify-between w-full">
                        <span>Students ({{ count($students) }})</span>
                        <div class="flex gap-2">
                            <x-filament::button
                                wire:click="generateBulkPdf"
                                color="primary"
                                size="sm"
                            >
                                <x-heroicon-o-arrow-down-tray class="w-4 h-4 mr-1" />
                                Download All (ZIP)
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
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Results
                                </th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Average
                                </th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Comments
                                </th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Last Generated
                                </th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Actions
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
                                    <td class="px-4 py-3 text-center">
                                        @if($student['results_count'] > 0)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                {{ $student['results_count'] }} subjects
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                No results
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($student['average'] > 0)
                                            <span class="font-semibold
                                                @if($student['average'] >= 75) text-green-600
                                                @elseif($student['average'] >= 50) text-yellow-600
                                                @else text-red-600
                                                @endif
                                            ">
                                                {{ number_format($student['average'], 1) }}%
                                            </span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            @if($student['has_class_teacher_comment'])
                                                <span class="inline-flex items-center p-1 rounded bg-blue-100 dark:bg-blue-900" title="Class Teacher Comment">
                                                    <x-heroicon-s-chat-bubble-left class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                                                </span>
                                            @endif
                                            @if($student['has_head_teacher_comment'])
                                                <span class="inline-flex items-center p-1 rounded bg-purple-100 dark:bg-purple-900" title="Head Teacher Comment">
                                                    <x-heroicon-s-academic-cap class="w-4 h-4 text-purple-600 dark:text-purple-400" />
                                                </span>
                                            @endif
                                            @if(!$student['has_class_teacher_comment'] && !$student['has_head_teacher_comment'])
                                                <span class="text-gray-400 text-xs">None</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center text-sm text-gray-500 dark:text-gray-400">
                                        {{ $student['last_generated'] ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            {{-- Add/Edit Comment --}}
                                            <button
                                                wire:click="openCommentModal({{ $student['id'] }})"
                                                class="inline-flex items-center p-2 rounded-lg text-gray-600 hover:text-blue-600 hover:bg-blue-50 dark:text-gray-400 dark:hover:text-blue-400 dark:hover:bg-blue-900/20 transition"
                                                title="Add/Edit Comments"
                                            >
                                                <x-heroicon-o-chat-bubble-left-ellipsis class="w-5 h-5" />
                                            </button>

                                            {{-- Preview --}}
                                            <button
                                                wire:click="previewReport({{ $student['id'] }})"
                                                class="inline-flex items-center p-2 rounded-lg text-gray-600 hover:text-green-600 hover:bg-green-50 dark:text-gray-400 dark:hover:text-green-400 dark:hover:bg-green-900/20 transition"
                                                title="Preview Report Card"
                                            >
                                                <x-heroicon-o-eye class="w-5 h-5" />
                                            </button>

                                            {{-- Download PDF --}}
                                            <button
                                                wire:click="generatePdf({{ $student['id'] }})"
                                                class="inline-flex items-center p-2 rounded-lg text-gray-600 hover:text-primary-600 hover:bg-primary-50 dark:text-gray-400 dark:hover:text-primary-400 dark:hover:bg-primary-900/20 transition"
                                                title="Download PDF"
                                                @if($student['results_count'] == 0) disabled @endif
                                            >
                                                <x-heroicon-o-arrow-down-tray class="w-5 h-5" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Summary --}}
                <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                        <div>
                            <div class="text-2xl font-bold text-gray-600 dark:text-gray-300">
                                {{ count($students) }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Total Students</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-green-600">
                                {{ collect($students)->filter(fn($s) => $s['results_count'] > 0)->count() }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">With Results</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-blue-600">
                                {{ collect($students)->filter(fn($s) => $s['has_class_teacher_comment'])->count() }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">With Comments</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-purple-600">
                                {{ collect($students)->filter(fn($s) => $s['last_generated'])->count() }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Generated</div>
                        </div>
                    </div>
                </div>
            </x-filament::section>
        @else
            <x-filament::section>
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No students loaded</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Please select a class, term, and year to generate report cards
                    </p>
                </div>
            </x-filament::section>
        @endif
    </div>

    {{-- Comment Modal --}}
    <x-filament::modal id="comment-modal" width="lg">
        <x-slot name="heading">
            Add/Edit Comments
        </x-slot>

        <div class="space-y-4">
            {{-- Class Teacher Comment --}}
            <div>
                <label for="classTeacherComment" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Class Teacher's Comment
                </label>
                <textarea
                    id="classTeacherComment"
                    wire:model="classTeacherComment"
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg
                           bg-white dark:bg-gray-800 text-gray-900 dark:text-white
                           focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                    placeholder="Enter class teacher's comment for this student..."
                ></textarea>
            </div>

            {{-- Head Teacher Comment (Admin Only) --}}
            @if(auth()->user()->role_id === \App\Constants\RoleConstants::ADMIN)
                <div>
                    <label for="headTeacherComment" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Head Teacher's Comment
                    </label>
                    <textarea
                        id="headTeacherComment"
                        wire:model="headTeacherComment"
                        rows="3"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg
                               bg-white dark:bg-gray-800 text-gray-900 dark:text-white
                               focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                        placeholder="Enter head teacher's comment for this student..."
                    ></textarea>
                </div>
            @endif

            {{-- Quick Comment Templates --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Quick Templates
                </label>
                <div class="flex flex-wrap gap-2">
                    <button
                        type="button"
                        wire:click="$set('classTeacherComment', 'An excellent performance this term. Keep up the good work!')"
                        class="text-xs px-3 py-1 bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 rounded-full hover:bg-green-200 dark:hover:bg-green-800 transition"
                    >
                        Excellent
                    </button>
                    <button
                        type="button"
                        wire:click="$set('classTeacherComment', 'Good performance overall. Continue to work hard and aim higher.')"
                        class="text-xs px-3 py-1 bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 rounded-full hover:bg-blue-200 dark:hover:bg-blue-800 transition"
                    >
                        Good
                    </button>
                    <button
                        type="button"
                        wire:click="$set('classTeacherComment', 'Satisfactory performance. More effort is needed to improve grades.')"
                        class="text-xs px-3 py-1 bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 rounded-full hover:bg-yellow-200 dark:hover:bg-yellow-800 transition"
                    >
                        Satisfactory
                    </button>
                    <button
                        type="button"
                        wire:click="$set('classTeacherComment', 'Needs significant improvement. Please ensure more focus on studies.')"
                        class="text-xs px-3 py-1 bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 rounded-full hover:bg-red-200 dark:hover:bg-red-800 transition"
                    >
                        Needs Improvement
                    </button>
                </div>
            </div>
        </div>

        <x-slot name="footerActions">
            <x-filament::button
                wire:click="saveComments"
                color="primary"
            >
                Save Comments
            </x-filament::button>
        </x-slot>
    </x-filament::modal>

    {{-- JavaScript for opening preview in new tab --}}
    @push('scripts')
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('open-preview', ({ url }) => {
                window.open(url, '_blank');
            });
        });
    </script>
    @endpush
</x-filament-panels::page>
