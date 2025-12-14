<x-filament-panels::page>
    @php
        $homework = $this->homework;
        $student = $this->student;
        $submission = $this->getSubmission();
    @endphp

    <div class="space-y-6">
        <!-- Homework Header Card -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <!-- Status Banner -->
            <div class="px-6 py-3 {{ $homework->due_date->isPast() ? 'bg-red-500' : 'bg-primary-500' }}">
                <div class="flex items-center justify-between">
                    <span class="text-white font-medium">
                        @if($homework->due_date->isPast())
                            Past Due
                        @elseif($homework->due_date->isToday())
                            Due Today
                        @else
                            Due in {{ $homework->due_date->diffForHumans() }}
                        @endif
                    </span>
                    <span class="text-white text-sm">
                        {{ $homework->due_date->format('l, F d, Y') }}
                    </span>
                </div>
            </div>

            <div class="p-6">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $homework->title }}</h1>
                        <div class="mt-3 flex flex-wrap gap-3">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                <x-heroicon-o-book-open class="w-4 h-4 mr-1.5" />
                                {{ $homework->subject->name ?? 'N/A' }}
                            </span>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                <x-heroicon-o-academic-cap class="w-4 h-4 mr-1.5" />
                                {{ $homework->grade->name ?? 'N/A' }}
                            </span>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                <x-heroicon-o-user class="w-4 h-4 mr-1.5" />
                                {{ $homework->assignedBy->name ?? 'Teacher' }}
                            </span>
                        </div>
                    </div>

                    <!-- Submission Status -->
                    <div class="ml-4">
                        @if($submission)
                            <div class="text-center p-3 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700">
                                <x-heroicon-o-check-circle class="w-8 h-8 mx-auto text-green-500" />
                                <span class="block text-sm font-medium text-green-700 dark:text-green-400 mt-1">Submitted</span>
                                @if($submission->marks)
                                    <span class="block text-lg font-bold text-green-600 dark:text-green-300">{{ $submission->marks }}%</span>
                                @endif
                            </div>
                        @else
                            <div class="text-center p-3 rounded-lg bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700">
                                <x-heroicon-o-clock class="w-8 h-8 mx-auto text-yellow-500" />
                                <span class="block text-sm font-medium text-yellow-700 dark:text-yellow-400 mt-1">Pending</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Instructions Section -->
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-document-text class="w-5 h-5 text-primary-500" />
                    <span>Instructions</span>
                </div>
            </x-slot>

            <div class="prose dark:prose-invert max-w-none">
                @if($homework->description)
                    <div class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap leading-relaxed">{{ $homework->description }}</div>
                @else
                    <p class="text-gray-500 dark:text-gray-400 italic">No instructions provided.</p>
                @endif
            </div>
        </x-filament::section>

        <!-- Homework Resources/Files -->
        @if($homework->homework_file)
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-paper-clip class="w-5 h-5 text-primary-500" />
                    <span>Homework Resources</span>
                </div>
            </x-slot>

            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-12 h-12 rounded-lg bg-blue-100 dark:bg-blue-800 flex items-center justify-center mr-4">
                            <x-heroicon-o-document-arrow-down class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 dark:text-white">Homework Document</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Download to view the complete homework</p>
                        </div>
                    </div>
                    <a href="{{ route('homework.download', $homework) }}"
                       target="_blank"
                       class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-150 ease-in-out shadow-sm">
                        <x-heroicon-o-arrow-down-tray class="w-5 h-5 mr-2" />
                        Download
                    </a>
                </div>
            </div>
        </x-filament::section>
        @endif

        <!-- Submission Section -->
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-arrow-up-tray class="w-5 h-5 text-primary-500" />
                    <span>Your Submission</span>
                </div>
            </x-slot>

            @if($submission)
                <div class="space-y-4">
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg p-4">
                        <div class="flex items-start">
                            <x-heroicon-o-check-circle class="w-6 h-6 text-green-500 mr-3 mt-0.5" />
                            <div class="flex-1">
                                <h4 class="font-medium text-green-800 dark:text-green-200">Homework Submitted</h4>
                                <p class="text-sm text-green-700 dark:text-green-300 mt-1">
                                    Submitted on {{ $submission->created_at->format('F d, Y \a\t h:i A') }}
                                </p>
                                @if($submission->marks)
                                    <div class="mt-3 p-3 bg-white dark:bg-gray-800 rounded-lg">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Grade:</span>
                                        <span class="text-2xl font-bold text-primary-600 dark:text-primary-400 ml-2">{{ $submission->marks }}%</span>
                                        @if($submission->feedback)
                                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                                <strong>Feedback:</strong> {{ $submission->feedback }}
                                            </p>
                                        @endif
                                    </div>
                                @else
                                    <p class="mt-2 text-sm text-yellow-600 dark:text-yellow-400">
                                        <x-heroicon-o-clock class="w-4 h-4 inline mr-1" />
                                        Awaiting grading by teacher
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($submission->submission_file)
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="flex items-center">
                                <x-heroicon-o-document class="w-5 h-5 text-gray-500 mr-2" />
                                <span class="text-sm text-gray-700 dark:text-gray-300">Your submitted file</span>
                            </div>
                            <a href="{{ route('homework-submissions.download', $submission) }}"
                               class="text-primary-600 hover:text-primary-500 text-sm font-medium">
                                View Submission
                            </a>
                        </div>
                    @endif
                </div>
            @else
                <div class="text-center py-8">
                    <x-heroicon-o-document-plus class="w-12 h-12 mx-auto text-gray-400 mb-4" />
                    <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No Submission Yet</h4>
                    <p class="text-gray-500 dark:text-gray-400 mb-4">You haven't submitted this homework yet.</p>

                    @if(!$homework->due_date->isPast())
                        <a href="{{ route('filament.admin.resources.homework-submissions.create', ['homework_id' => $homework->id]) }}"
                           class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition duration-150 ease-in-out">
                            <x-heroicon-o-arrow-up-tray class="w-5 h-5 mr-2" />
                            Submit Homework
                        </a>
                    @else
                        <p class="text-red-500 dark:text-red-400 text-sm">
                            <x-heroicon-o-exclamation-circle class="w-4 h-4 inline mr-1" />
                            This homework is past due
                        </p>
                    @endif
                </div>
            @endif
        </x-filament::section>

        <!-- Back Button -->
        <div class="flex justify-center pt-4">
            <a href="/admin/student-dashboard"
               class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-medium rounded-lg transition duration-150 ease-in-out">
                <x-heroicon-o-arrow-left class="w-5 h-5 mr-2" />
                Back to Dashboard
            </a>
        </div>
    </div>
</x-filament-panels::page>
