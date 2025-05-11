<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <!-- Class Overview Section -->
        <x-filament::section>
            <x-slot name="heading">
                My Classes
            </x-slot>

            <div class="space-y-4">
                @forelse($this->getAssignedClasses() as $class)
                    <div class="flex items-center p-4 bg-white rounded-lg shadow-sm dark:bg-gray-800">
                        <div class="p-2 mr-4 rounded-lg bg-primary-100">
                            <x-heroicon-o-academic-cap class="w-8 h-8 text-primary-500" />
                        </div>
                        <div>
                            <h3 class="text-lg font-medium">{{ $class->name }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $class->grade?->name ?? 'No Grade' }}</p>
                            @if($this->isClassTeacher() && $this->getTeacher()?->class_section_id === $class->id)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100">
                                    Class Teacher
                                </span>
                            @endif
                        </div>
                        <div class="ml-auto text-center">
                            <span class="text-2xl font-bold text-gray-700 dark:text-gray-300">
                                {{ $class->students?->count() ?? 0 }}
                            </span>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Students</p>
                        </div>
                        <div class="ml-4">
                            <a href="{{ route('filament.admin.resources.class-rooms.view', ['record' => $class->id]) }}"
                               class="text-primary-600 hover:text-primary-500 dark:text-primary-500 dark:hover:text-primary-400">
                                View Details
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
                        <p class="text-center text-gray-500 dark:text-gray-400">No classes assigned yet</p>
                    </div>
                @endforelse
            </div>
        </x-filament::section>

        <!-- Grading Summary Section -->
        <x-filament::section>
            <x-slot name="heading">
                Grading Overview
            </x-slot>

            <div class="space-y-4">
                @php
                    $summary = $this->getGradingSummary();
                @endphp

                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 bg-blue-50 rounded-lg dark:bg-blue-900/20">
                        <div class="text-sm text-blue-600 dark:text-blue-400">Total Submissions</div>
                        <div class="text-2xl font-bold text-blue-700 dark:text-blue-300">{{ $summary['total_submitted'] }}</div>
                    </div>
                    <div class="p-4 bg-yellow-50 rounded-lg dark:bg-yellow-900/20">
                        <div class="text-sm text-yellow-600 dark:text-yellow-400">Pending Grading</div>
                        <div class="text-2xl font-bold text-yellow-700 dark:text-yellow-300">{{ $summary['ungraded'] }}</div>
                    </div>
                    <div class="p-4 bg-green-50 rounded-lg dark:bg-green-900/20">
                        <div class="text-sm text-green-600 dark:text-green-400">Graded</div>
                        <div class="text-2xl font-bold text-green-700 dark:text-green-300">{{ $summary['graded'] }}</div>
                    </div>
                    <div class="p-4 bg-red-50 rounded-lg dark:bg-red-900/20">
                        <div class="text-sm text-red-600 dark:text-red-400">Late Submissions</div>
                        <div class="text-2xl font-bold text-red-700 dark:text-red-300">{{ $summary['late'] }}</div>
                    </div>
                </div>

                <div class="mt-4">
                    <a href="{{ route('filament.admin.resources.teacher-homework-submissions.index') }}"
                       class="inline-flex items-center text-sm text-primary-600 hover:text-primary-500">
                        Grade pending submissions →
                    </a>
                </div>
            </div>
        </x-filament::section>
    </div>

    <!-- Active Homework Section -->
    <x-filament::section class="mt-6">
        <x-slot name="heading">
            Active Homework
        </x-slot>

        <div class="space-y-4">
            @forelse($this->getAssignedHomework() as $homework)
                <div class="flex items-center p-4 bg-white rounded-lg shadow-sm dark:bg-gray-800">
                    <div class="min-w-16 text-center mr-4">
                        <div class="text-lg font-bold text-primary-600 dark:text-primary-500">{{ $homework->due_date->format('d') }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $homework->due_date->format('M') }}</div>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-medium text-gray-900 dark:text-white">{{ $homework->title }}</h4>
                        <div class="mt-1 flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                            <span>{{ $homework->subject->name }}</span>
                            <span>•</span>
                            <span>{{ $homework->grade->name }}</span>
                            <span>•</span>
                            <span>Due {{ $homework->due_date->diffForHumans() }}</span>
                        </div>
                    </div>
                    <div class="ml-auto flex items-center gap-4">
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $homework->submissions()->count() }} submissions
                        </span>
                        <a href="{{ route('filament.admin.resources.teacher-homework.view', ['record' => $homework->id]) }}"
                           class="text-primary-600 hover:text-primary-500 text-sm">
                            View →
                        </a>
                    </div>
                </div>
            @empty
                <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
                    <p class="text-center text-gray-500 dark:text-gray-400">No active homework</p>
                </div>
            @endforelse
        </div>
    </x-filament::section>

    <!-- Recent Submissions Section -->
    <x-filament::section class="mt-6">
        <x-slot name="heading">
            Recent Submissions
        </x-slot>

        <div class="space-y-4">
            @forelse($this->getRecentSubmissions() as $submission)
                <div class="flex items-center p-4 bg-white rounded-lg shadow-sm dark:bg-gray-800">
                    <div class="flex-1">
                        <h4 class="font-medium text-gray-900 dark:text-white">{{ $submission->student->name }}</h4>
                        <div class="mt-1 flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                            <span>{{ $submission->homework->title }}</span>
                            <span>•</span>
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
                    <div class="ml-4 flex items-center gap-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                            {{ ucfirst($submission->status) }}
                        </span>
                        <a href="{{ route('filament.admin.resources.teacher-homework-submissions.edit', ['record' => $submission->id]) }}"
                           class="text-primary-600 hover:text-primary-500 text-sm">
                            Grade →
                        </a>
                    </div>
                </div>
            @empty
                <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
                    <p class="text-center text-gray-500 dark:text-gray-400">No recent submissions</p>
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
                    <div class="min-w-20 mr-4 text-center">
                        <div class="text-lg font-bold text-primary-600 dark:text-primary-500">{{ $event->start_date->format('d') }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $event->start_date->format('M') }}</div>
                    </div>
                    <div class="h-12 w-px bg-gray-200 dark:bg-gray-700 mr-4"></div>
                    <div class="ml-2 flex-1">
                        <h4 class="font-medium text-base">{{ $event->title }}</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ Str::limit($event->description, 100) }}</p>
                        @if($event->location)
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                <span class="inline-flex items-center">
                                    <x-heroicon-o-map-pin class="w-3 h-3 mr-1" />
                                    {{ $event->location }}
                                </span>
                            </p>
                        @endif
                    </div>
                    <div class="ml-auto">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $event->start_date->isPast() ? 'bg-gray-100 text-gray-800' : 'bg-primary-100 text-primary-800' }}">
                            {{ $event->is_all_day ? 'All Day' : $event->start_date->format('h:i A') }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
                    <p class="text-center text-gray-500 dark:text-gray-400">No upcoming events</p>
                </div>
            @endforelse

            <div class="text-right">
                @if(Route::has('filament.admin.resources.events.index'))
                    <a href="{{ route('filament.admin.resources.events.index') }}" class="text-sm text-primary-600 hover:underline">
                        View All Events
                    </a>
                @endif
            </div>
        </div>
    </x-filament::section>
</x-filament-panels::page>
