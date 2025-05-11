<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <!-- Children Overview Section -->
        <x-filament::section>
            <x-slot name="heading">
                My Children
            </x-slot>

            <div class="space-y-4">
                @forelse($this->getStudents() as $student)
                    <div class="flex items-center p-4 bg-white rounded-lg shadow-sm dark:bg-gray-800">
                        @if($student->profile_photo)
                            <img src="{{ Storage::url($student->profile_photo) }}" alt="{{ $student->name }}" class="w-12 h-12 rounded-full mr-4">
                        @else
                            <div class="w-12 h-12 rounded-full bg-primary-100 flex items-center justify-center mr-4">
                                <span class="text-primary-600 font-bold text-xl">{{ substr($student->name, 0, 1) }}</span>
                            </div>
                        @endif
                        <div>
                            <h3 class="text-lg font-medium">{{ $student->name }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $student->grade?->name ?? 'No Grade' }} {{ $student->classSection?->name ?? '' }}
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">ID: {{ $student->student_id_number }}</p>
                        </div>
                        <div class="ml-auto">
                            <a href="{{ route('filament.admin.resources.students.view', ['record' => $student->id]) }}" class="text-primary-600 hover:text-primary-500 dark:text-primary-500 dark:hover:text-primary-400">
                                View Details
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
                        <p class="text-center text-gray-500 dark:text-gray-400">No children registered</p>
                    </div>
                @endforelse
            </div>
        </x-filament::section>

        <!-- Recent Fee Payments Section -->
        <x-filament::section>
            <x-slot name="heading">
                Recent Fee Payments
            </x-slot>

            <div class="space-y-4">
                @forelse($this->getFeePayments() as $payment)
                    <div class="p-4 bg-white rounded-lg shadow-sm dark:bg-gray-800">
                        <div class="flex justify-between items-center">
                            <h4 class="font-medium">{{ $payment->student->name }}</h4>
                            <span class="text-sm font-semibold text-primary-600 dark:text-primary-500">
                                K{{ number_format($payment->amount, 2) }}
                            </span>
                        </div>
                        <div class="mt-2 flex justify-between text-sm text-gray-500 dark:text-gray-400">
                            <span>{{ $payment->student->grade?->name ?? 'No Grade' }} - {{ $payment->description ?? 'School Fees' }}</span>
                            <span>{{ $payment->payment_date->format('M d, Y') }}</span>
                        </div>
                        <div class="mt-2 text-right">
                            <a href="{{ route('filament.admin.resources.fee-payments.view', ['record' => $payment->id]) }}" class="text-sm text-primary-600 hover:text-primary-500 dark:text-primary-500 dark:hover:text-primary-400">
                                View Receipt
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
                        <p class="text-center text-gray-500 dark:text-gray-400">No recent payments</p>
                    </div>
                @endforelse
            </div>
        </x-filament::section>
    </div>

    <!-- Recent Homework Submissions Section -->
    <x-filament::section class="mt-6">
        <x-slot name="heading">
            Recent Homework Submissions
        </x-slot>

        <div class="space-y-4">
            @forelse($this->getRecentHomework() as $submission)
                <div class="flex items-center p-4 bg-white rounded-lg shadow-sm dark:bg-gray-800">
                    <div class="flex-1">
                        <h4 class="font-medium text-gray-900 dark:text-white">{{ $submission->homework->title }}</h4>
                        <div class="mt-1 flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                            <span>{{ $submission->student->name }}</span>
                            <span>•</span>
                            <span>{{ $submission->homework->subject->name }}</span>
                            <span>•</span>
                            <span>Due: {{ $submission->homework->due_date->format('M d, Y') }}</span>
                        </div>
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
                    <p class="text-center text-gray-500 dark:text-gray-400">No recent homework submissions</p>
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
                        <h4 class="font-medium text-gray-900 dark:text-white">{{ $result->student->name }}</h4>
                        <div class="mt-1 flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                            <span>{{ $result->subject->name }}</span>
                            <span>•</span>
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
            Upcoming School Events
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
                    <div class="ml-auto">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $event->start_date->isPast() ? 'bg-gray-100 text-gray-800' : 'bg-primary-100 text-primary-800' }}">
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
