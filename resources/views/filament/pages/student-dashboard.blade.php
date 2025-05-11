<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <!-- Academic Summary Section -->
        <x-filament::section>
            <x-slot name="heading">
                Academic Overview
            </x-slot>

            <div class="space-y-4">
                @php
                    $summary = $this->getAcademicSummary();
                @endphp

                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 bg-blue-50 rounded-lg dark:bg-blue-900/20">
                        <div class="text-sm text-blue-600 dark:text-blue-400">Total Homework</div>
                        <div class="text-2xl font-bold text-blue-700 dark:text-blue-300">{{ $summary['total_homework'] }}</div>
                    </div>
                    <div class="p-4 bg-green-50 rounded-lg dark:bg-green-900/20">
                        <div class="text-sm text-green-600 dark:text-green-400">Submitted</div>
                        <div class="text-2xl font-bold text-green-700 dark:text-green-300">{{ $summary['submitted'] }}</div>
                    </div>
                    <div class="p-4 bg-orange-50 rounded-lg dark:bg-orange-900/20">
                        <div class="text-sm text-orange-600 dark:text-orange-400">Pending</div>
                        <div class="text-2xl font-bold text-orange-700 dark:text-orange-300">{{ $summary['pending'] }}</div>
                    </div>
                    <div class="p-4 bg-purple-50 rounded-lg dark:bg-purple-900/20">
                        <div class="text-sm text-purple-600 dark:text-purple-400">Average Grade</div>
                        <div class="text-2xl font-bold text-purple-700 dark:text-purple-300">
                            {{ $summary['average_grade'] ? number_format($summary['average_grade'], 1) . '%' : 'N/A' }}
                        </div>
                    </div>
                </div>
            </div>
        </x-filament::section>

        <!-- Pending Homework Section -->
        <x-filament::section>
            <x-slot name="heading">
                Pending Homework
            </x-slot>

            <div class="space-y-4">
                @forelse($this->getPendingHomework() as $homework)
                    <div class="p-4 bg-white rounded-lg shadow-sm dark:bg-gray-800 border {{ $homework->due_date->isPast() ? 'border-red-200 dark:border-red-800' : 'border-gray-200 dark:border-gray-700' }}">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white">{{ $homework->title }}</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $homework->subject->name }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-500 mt-1">Due: {{ $homework->due_date->format('M d, Y') }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                @if($homework->due_date->isPast())
                                    <span class="text-xs px-2 py-1 rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                        Overdue
                                    </span>
                                @else
                                    <span class="text-xs px-2 py-1 rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        Due {{ $homework->due_date->diffForHumans() }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="mt-3">
                            <a href="{{ route('filament.admin.resources.teacher-homework.view', ['record' => $homework->id]) }}"
                               class="text-primary-600 hover:text-primary-500 text-sm">
                                View Homework →
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
                        <p class="text-center text-gray-500 dark:text-gray-400">No pending homework</p>
                    </div>
                @endforelse
            </div>
        </x-filament::section>
    </div>

    <!-- Recent Submissions Section -->
    <x-filament::section class="mt-6">
        <x-slot name="heading">
            Recent Submissions
        </x-slot>

        <div class="space-y-4">
            @forelse($this->getRecentHomeworkSubmissions() as $submission)
                <div class="flex items-center p-4 bg-white rounded-lg shadow-sm dark:bg-gray-800">
                    <div class="flex-1">
                        <h4 class="font-medium text-gray-900 dark:text-white">{{ $submission->homework->title }}</h4>
                        <div class="mt-1 flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                            <span>{{ $submission->homework->subject->name }}</span>
                            <span>•</span>
                            <span>Submitted {{ $submission->submitted_at->diffForHumans() }}</span>
                        </div>
                        @if($submission->is_late)
                            <span class="inline-flex items-center text-xs text-red-600 dark:text-red-400 mt-1">
                                <x-heroicon-o-clock class="w-3 h-3 mr-1" />
                                Late submission
                            </span>
                        @endif
                    </div>
                    <div class="ml-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $submission->status === 'graded' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' :
                               ($submission->status === 'submitted' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300' :
                                'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300') }}">
                            {{ ucfirst($submission->status) }}
                            @if($submission->status === 'graded' && $submission->marks !== null)
                                - {{ $submission->marks }}/{{ $submission->homework->max_score }}
                            @endif
                        </span>
                    </div>
                </div>
            @empty
                <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
                    <p class="text-center text-gray-500 dark:text-gray-400">No recent submissions</p>
                </div>
            @endforelse
        </div>
    </x-filament::section>

    <!-- Recent Results Section -->
    <x-filament::section class="mt-6">
        <x-slot name="heading">
            Recent Results
        </x-slot>

        <div class="space-y-4">
            @forelse($this->getRecentResults() as $result)
                <div class="flex items-center p-4 bg-white rounded-lg shadow-sm dark:bg-gray-800">
                    <div class="flex-1">
                        <h4 class="font-medium text-gray-900 dark:text-white">{{ $result->subject->name }}</h4>
                        <div class="mt-1 flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                            <span>{{ $result->exam_type }}</span>
                            <span>•</span>
                            <span>{{ $result->term }} {{ $result->year }}</span>
                        </div>
                    </div>
                    <div class="ml-4 text-right">
                        <div class="text-lg font-bold text-gray-900 dark:text-white">{{ $result->grade }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $result->marks }}%</div>
                    </div>
                </div>
            @empty
                <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
                    <p class="text-center text-gray-500 dark:text-gray-400">No recent results</p>
                </div>
            @endforelse
        </div>
    </x-filament::section>

    <!-- Upcoming Events Section -->
    <x-filament::section class="mt-6">
        <x-slot name="heading">
            Upcoming Events
        </x-slot>

        <div class="space-y-4">
            @forelse($this->getUpcomingEvents() as $event)
                <div class="flex items-center p-4 bg-white rounded-lg shadow-sm dark:bg-gray-800">
                    <div class="min-w-16 text-center mr-4">
                        <div class="text-lg font-bold text-primary-600 dark:text-primary-500">{{ $event->start_date->format('d') }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $event->start_date->format('M') }}</div>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-medium text-gray-900 dark:text-white">{{ $event->title }}</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ Str::limit($event->description, 100) }}</p>
                    </div>
                    <div class="ml-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-300">
                            {{ $event->start_date->format('h:i A') }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
                    <p class="text-center text-gray-500 dark:text-gray-400">No upcoming events</p>
                </div>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-panels::page>
