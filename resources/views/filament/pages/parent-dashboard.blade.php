<x-filament-panels::page>
    @php
        $parentGuardian = $this->getParentGuardian();
        $students = $this->getStudents();
        $stats = $this->getDashboardStats();
        $recentHomework = $this->getRecentHomework();
        $feePayments = $this->getFeePayments();
        $recentResults = $this->getRecentResults();
        $upcomingEvents = $this->getUpcomingEvents();
    @endphp

    {{-- Dashboard Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        {{-- Children Count --}}
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-academic-cap class="h-8 w-8 text-blue-500" />
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                My Children
                            </dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-semibold text-gray-900 dark:text-white">
                                    {{ $stats['children_count'] }}
                                </div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pending Homework --}}
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-document-text class="h-8 w-8 text-yellow-500" />
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                Pending Homework
                            </dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-semibold text-gray-900 dark:text-white">
                                    {{ $stats['pending_homework'] }}
                                </div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        {{-- Overdue Homework --}}
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-exclamation-triangle class="h-8 w-8 text-red-500" />
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                Overdue Homework
                            </dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-semibold text-red-600">
                                    {{ $stats['overdue_homework'] }}
                                </div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        {{-- Upcoming Events --}}
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-calendar class="h-8 w-8 text-green-500" />
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                Upcoming Events
                            </dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-semibold text-gray-900 dark:text-white">
                                    {{ $stats['upcoming_events'] }}
                                </div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- My Children --}}
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white flex items-center">
                    <x-heroicon-o-academic-cap class="h-5 w-5 mr-2 text-blue-500" />
                    My Children
                </h3>
                <div class="mt-4">
                    @if($students->count() > 0)
                        <div class="space-y-3">
                            @foreach($students as $student)
                                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $student->name }}
                                        </p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $student->grade->name ?? 'No Grade' }}
                                            @if($student->classSection)
                                                - {{ $student->classSection->name }}
                                            @endif
                                        </p>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            {{ ucfirst($student->enrollment_status) }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">No children enrolled.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Recent Homework --}}
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white flex items-center">
                    <x-heroicon-o-document-text class="h-5 w-5 mr-2 text-yellow-500" />
                    Recent Homework
                </h3>
                <div class="mt-4">
                    @if($recentHomework->count() > 0)
                        <div class="space-y-3">
                            @foreach($recentHomework->take(3) as $homework)
                                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $homework->title }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $homework->subject->name }} • {{ $homework->grade->name }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            Due: {{ $homework->due_date->format('M j, g:i A') }}
                                        </p>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        @if($homework->homework_file)
                                            <a href="{{ route('homework.download', $homework) }}"
                                               class="text-blue-600 hover:text-blue-800 text-xs">
                                                Download
                                            </a>
                                        @endif
                                        @if($homework->due_date->isPast())
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Overdue
                                            </span>
                                        @elseif($homework->due_date->diffInDays() <= 2)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Due Soon
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @if($recentHomework->count() > 3)
                            <div class="mt-3 text-center">
                                <a href="{{ route('filament.admin.resources.homework.index') }}"
                                   class="text-sm text-blue-600 hover:text-blue-800">
                                    View all homework ({{ $recentHomework->count() }} total)
                                </a>
                            </div>
                        @endif
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">No recent homework.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Recent Fee Payments --}}
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white flex items-center">
                    <x-heroicon-o-banknotes class="h-5 w-5 mr-2 text-green-500" />
                    Recent Payments
                </h3>
                <div class="mt-4">
                    @if($feePayments->count() > 0)
                        <div class="space-y-3">
                            @foreach($feePayments as $payment)
                                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $payment->student->name }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $payment->payment_date->format('M j, Y') }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-medium text-green-600">
                                            K{{ number_format($payment->amount_paid, 2) }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ ucfirst($payment->payment_method) }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">No recent payments.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Upcoming Events --}}
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white flex items-center">
                    <x-heroicon-o-calendar class="h-5 w-5 mr-2 text-purple-500" />
                    Upcoming Events
                </h3>
                <div class="mt-4">
                    @if($upcomingEvents->count() > 0)
                        <div class="space-y-3">
                            @foreach($upcomingEvents as $event)
                                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $event->title }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $event->start_date->format('M j, Y g:i A') }}
                                        </p>
                                        @if($event->description)
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                {{ Str::limit($event->description, 50) }}
                                            </p>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            {{ $event->start_date->diffForHumans() }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">No upcoming events.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($stats['children_count'] === 0)
        {{-- No Children Enrolled Message --}}
        <div class="mt-6 bg-yellow-50 dark:bg-yellow-900/50 border border-yellow-200 dark:border-yellow-700 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <x-heroicon-o-exclamation-triangle class="h-5 w-5 text-yellow-400" />
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                        No Children Enrolled
                    </h3>
                    <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                        <p>
                            It looks like you don't have any children enrolled at the school yet.
                            Please contact the school administration to complete the enrollment process.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
