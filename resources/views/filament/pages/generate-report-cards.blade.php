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
                            {{-- Import from Excel --}}
                            <x-filament::button
                                wire:click="openImportModal"
                                color="gray"
                                size="sm"
                            >
                                <x-heroicon-o-arrow-up-tray class="w-4 h-4 mr-1" />
                                Import Results
                            </x-filament::button>

                            {{-- Send SMS to Parents --}}
                            <x-filament::button
                                wire:click="openSmsConfirmModal"
                                color="success"
                                size="sm"
                            >
                                <x-heroicon-o-chat-bubble-left-right class="w-4 h-4 mr-1" />
                                Send SMS to Parents
                            </x-filament::button>

                            {{-- Download All --}}
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

    {{-- Import Modal --}}
    <x-filament::modal id="import-modal" width="xl">
        <x-slot name="heading">
            Import Results from Excel/CSV
        </x-slot>

        <div class="space-y-4">
            {{-- Instructions --}}
            <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                <h4 class="font-medium text-blue-800 dark:text-blue-200 mb-2">Instructions:</h4>
                <ul class="text-sm text-blue-700 dark:text-blue-300 list-disc list-inside space-y-1">
                    <li>Download the template to see the required format</li>
                    <li>First column should be "Student Name" or "Name"</li>
                    <li>Second column (optional) should be "Student ID"</li>
                    <li>Subject columns should match subject names or codes (Math, Eng, Sci, etc.)</li>
                    <li>Marks should be numeric values between 0 and 100</li>
                </ul>
            </div>

            {{-- Download Template Button --}}
            <div class="flex justify-center">
                <x-filament::button
                    wire:click="downloadTemplate"
                    color="gray"
                    size="sm"
                >
                    <x-heroicon-o-document-arrow-down class="w-4 h-4 mr-1" />
                    Download Template
                </x-filament::button>
            </div>

            {{-- Exam Type Selection --}}
            <div>
                <label for="examType" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Exam Type
                </label>
                <select
                    id="examType"
                    wire:model="examType"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg
                           bg-white dark:bg-gray-800 text-gray-900 dark:text-white
                           focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                >
                    <option value="final">End of Term (Final)</option>
                    <option value="mid-term">Mid-Term</option>
                    <option value="assignment">Assignment</option>
                    <option value="quiz">Quiz</option>
                </select>
            </div>

            {{-- File Upload --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Upload Results File (Excel or CSV)
                </label>
                <input
                    type="file"
                    wire:model="resultsFile"
                    accept=".xlsx,.xls,.csv"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg
                           bg-white dark:bg-gray-800 text-gray-900 dark:text-white
                           focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                />
                <div wire:loading wire:target="resultsFile" class="mt-2 text-sm text-gray-500">
                    Uploading...
                </div>
            </div>

            {{-- Import Results Display --}}
            @if($importResults)
                <div class="p-4 rounded-lg {{ $importResults['success'] ? 'bg-green-50 dark:bg-green-900/20' : 'bg-red-50 dark:bg-red-900/20' }}">
                    <h4 class="font-medium {{ $importResults['success'] ? 'text-green-800 dark:text-green-200' : 'text-red-800 dark:text-red-200' }} mb-2">
                        {{ $importResults['success'] ? 'Import Results' : 'Import Failed' }}
                    </h4>
                    <p class="text-sm {{ $importResults['success'] ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300' }}">
                        {{ $importResults['message'] }}
                    </p>

                    @if(!empty($importResults['errors']))
                        <div class="mt-2">
                            <p class="text-sm font-medium text-red-700 dark:text-red-300">Errors:</p>
                            <ul class="text-xs text-red-600 dark:text-red-400 list-disc list-inside max-h-32 overflow-y-auto">
                                @foreach(array_slice($importResults['errors'], 0, 10) as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                                @if(count($importResults['errors']) > 10)
                                    <li>... and {{ count($importResults['errors']) - 10 }} more errors</li>
                                @endif
                            </ul>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <x-slot name="footerActions">
            <x-filament::button
                wire:click="closeImportModal"
                color="gray"
            >
                Cancel
            </x-filament::button>
            <x-filament::button
                wire:click="importResults"
                color="primary"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove wire:target="importResults">Import Results</span>
                <span wire:loading wire:target="importResults">Importing...</span>
            </x-filament::button>
        </x-slot>
    </x-filament::modal>

    {{-- SMS Confirmation Modal --}}
    <x-filament::modal id="sms-confirm-modal" width="xl">
        <x-slot name="heading">
            Send Results SMS to Parents
        </x-slot>

        <div class="space-y-4">
            {{-- Warning --}}
            <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                <div class="flex items-start">
                    <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mr-2 mt-0.5" />
                    <div>
                        <h4 class="font-medium text-yellow-800 dark:text-yellow-200">Confirm SMS Notifications</h4>
                        <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-1">
                            This will send SMS notifications to all parents with registered phone numbers.
                            SMS charges will apply based on your credit balance.
                        </p>
                    </div>
                </div>
            </div>

            {{-- SMS Preview --}}
            @if(!empty($smsPreview))
                <div>
                    <h4 class="font-medium text-gray-700 dark:text-gray-300 mb-2">SMS Preview (first 3 students):</h4>
                    <div class="space-y-3 max-h-64 overflow-y-auto">
                        @foreach($smsPreview as $preview)
                            <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                <div class="flex justify-between items-start mb-1">
                                    <span class="font-medium text-gray-900 dark:text-white text-sm">{{ $preview['student'] }}</span>
                                    <span class="text-xs text-gray-500">{{ $preview['length'] }} chars</span>
                                </div>
                                <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">To: {{ $preview['phone'] }}</p>
                                <p class="text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 p-2 rounded border border-gray-200 dark:border-gray-600">
                                    {{ $preview['message'] }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Summary --}}
            <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <div class="grid grid-cols-2 gap-4 text-center">
                    <div>
                        <div class="text-2xl font-bold text-primary-600">
                            {{ collect($students)->filter(fn($s) => $s['results_count'] > 0)->count() }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Students with Results</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-green-600">
                            {{ count($smsPreview) > 0 ? '~' . ceil(strlen($smsPreview[0]['message'] ?? '') / 160) : 1 }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">SMS Parts per Message</div>
                    </div>
                </div>
            </div>
        </div>

        <x-slot name="footerActions">
            <x-filament::button
                wire:click="closeSmsModal"
                color="gray"
            >
                Cancel
            </x-filament::button>
            <x-filament::button
                wire:click="sendSmsNotifications"
                color="success"
                wire:loading.attr="disabled"
            >
                <x-heroicon-o-paper-airplane class="w-4 h-4 mr-1" />
                <span wire:loading.remove wire:target="sendSmsNotifications">Send SMS Notifications</span>
                <span wire:loading wire:target="sendSmsNotifications">Sending...</span>
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
