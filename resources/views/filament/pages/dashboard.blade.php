<!-- resources/views/filament/pages/dashboard.blade.php -->
<x-filament-panels::page>
    @php
        $viewData = $this->getViewData();
        $stats = $viewData['stats'];
        $quickActions = $viewData['quickActions'];
        $recentActivity = $viewData['recentActivity'];
        $upcomingEvents = $viewData['upcomingEvents'];
        $chartData = $viewData['chartData'];
    @endphp

    <!-- Stats Overview with Enhanced Styling -->
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
        @foreach($stats as $stat)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-200 dark:border-gray-700">
                {{ $stat }}
            </div>
        @endforeach
    </div>

    <!-- Quick Actions -->
    @if(count($quickActions) > 0)
    <div class="mt-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Actions</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            @foreach($quickActions as $action)
                <a href="{{ $action['url'] }}"
                   class="flex flex-col items-center p-4 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-md transition-shadow border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-center w-12 h-12 mb-2 rounded-full bg-{{ $action['color'] }}-100 dark:bg-{{ $action['color'] }}-900">
                        @if($action['icon'])
                            <x-dynamic-component :component="$action['icon']" class="w-6 h-6 text-{{ $action['color'] }}-600 dark:text-{{ $action['color'] }}-400" />
                        @else
                            <x-heroicon-o-plus class="w-6 h-6 text-{{ $action['color'] }}-600 dark:text-{{ $action['color'] }}-400" />
                        @endif
                    </div>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $action['title'] }}</span>
                </a>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Main Content Grid with Charts -->
    <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Charts Section -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Student Enrollment Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Student Enrollment by Grade</h2>
                <div id="enrollment-chart" class="h-64"></div>
            </div>

            <!-- Fee Collection Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Fee Collection Status</h2>
                <div id="fee-collection-chart" class="h-64"></div>
            </div>

            <!-- Subject Performance Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Subject Performance</h2>
                <div id="subject-performance-chart" class="h-64"></div>
            </div>
        </div>

        <!-- Sidebar Content -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Recent Activity with Tabs -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Activity</h2>
                    <div class="flex space-x-2">
                        <button class="px-3 py-1 text-sm bg-gray-100 dark:bg-gray-700 rounded-full transition-colors" id="activity-tab-all">All</button>
                        <button class="px-3 py-1 text-sm bg-gray-100 dark:bg-gray-700 rounded-full transition-colors" id="activity-tab-students">Students</button>
                        <button class="px-3 py-1 text-sm bg-gray-100 dark:bg-gray-700 rounded-full transition-colors" id="activity-tab-payments">Payments</button>
                        <button class="px-3 py-1 text-sm bg-gray-100 dark:bg-gray-700 rounded-full transition-colors" id="activity-tab-submissions">Homework</button>
                    </div>
                </div>

                <div id="activity-all" class="activity-content">
                    <ul role="list" class="space-y-4">
                        @foreach($recentActivity['all'] as $activity)
                            <li class="flex items-start p-3 rounded-lg bg-gray-50 dark:bg-gray-700">
                                <div class="flex-shrink-0">
                                    @switch($activity['type'])
                                        @case('student')
                                            <div class="flex items-center justify-center w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full">
                                                <x-heroicon-o-academic-cap class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                                            </div>
                                            @break
                                        @case('payment')
                                            <div class="flex items-center justify-center w-8 h-8 bg-green-100 dark:bg-green-900 rounded-full">
                                                <x-heroicon-o-banknotes class="w-5 h-5 text-green-600 dark:text-green-400" />
                                            </div>
                                            @break
                                        @case('submission')
                                            <div class="flex items-center justify-center w-8 h-8 bg-yellow-100 dark:bg-yellow-900 rounded-full">
                                                <x-heroicon-o-document-text class="w-5 h-5 text-yellow-600 dark:text-yellow-400" />
                                            </div>
                                            @break
                                        @case('sms')
                                            <div class="flex items-center justify-center w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-full">
                                                <x-heroicon-o-chat-bubble-left class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                                            </div>
                                            @break
                                        @default
                                            <div class="flex items-center justify-center w-8 h-8 bg-gray-100 dark:bg-gray-800 rounded-full">
                                                <x-heroicon-o-clock class="w-5 h-5 text-gray-600 dark:text-gray-400" />
                                            </div>
                                    @endswitch
                                </div>
                                <div class="ml-3 flex-1">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $activity['name'] ?? $activity['recipient'] ?? 'Unknown' }}
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $activity['description'] }}
                                    </p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                        {{ $activity['time'] }}
                                    </p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Individual Activity Tabs Content -->
                @foreach(['students', 'payments', 'submissions'] as $activityType)
                    <div id="activity-{{ $activityType }}" class="activity-content hidden">
                        <ul role="list" class="space-y-4">
                            @foreach($recentActivity[$activityType] ?? [] as $item)
                                @include('filament.dashboard.partials.activity-item', ['item' => $item, 'type' => $activityType])
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>

            <!-- Upcoming Events -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Upcoming Events</h2>
                @if(count($upcomingEvents) > 0)
                    <div class="flow-root">
                        <ul role="list" class="space-y-4">
                            @foreach($upcomingEvents as $event)
                                <li class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <div class="flex items-center justify-center w-8 h-8 bg-indigo-100 dark:bg-indigo-900 rounded-full">
                                            <x-heroicon-o-calendar class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $event['title'] }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ \Carbon\Carbon::parse($event['start_date'])->format('M d, Y') }}
                                            @if(isset($event['location']) && $event['location'])
                                                | {{ $event['location'] }}
                                            @endif
                                        </p>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">No upcoming events</p>
                @endif
                <div class="mt-3">
                    @if(Route::has('filament.admin.resources.events.index'))
                        <a href="{{ route('filament.admin.resources.events.index') }}" class="text-primary-500 hover:underline text-sm">View all events</a>
                    @endif
                </div>
            </div>

            <!-- System Status -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">System Status</h2>
                <div class="space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-400">SMS Balance</span>
                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-400">Active</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Database</span>
                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-400">Connected</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Storage</span>
                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-400">88% Free</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Last Backup</span>
                        <span class="text-sm text-gray-900 dark:text-white">{{ now()->subDay()->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Charts if data is available
            @if(isset($chartData))
                // Enrollment Chart
                const enrollmentData = @json($chartData['gradeData'] ?? []);
                if (enrollmentData.length > 0 && document.querySelector("#enrollment-chart")) {
                    const enrollmentCategories = enrollmentData.map(item => item.grade);
                    const enrollmentValues = enrollmentData.map(item => item.count);

                    new ApexCharts(document.querySelector("#enrollment-chart"), {
                        series: [{
                            name: 'Students',
                            data: enrollmentValues
                        }],
                        chart: {
                            type: 'bar',
                            height: 250,
                            toolbar: {
                                show: false
                            }
                        },
                        plotOptions: {
                            bar: {
                                borderRadius: 4,
                                columnWidth: '50%',
                            }
                        },
                        dataLabels: {
                            enabled: false
                        },
                        colors: ['#4F46E5'],
                        xaxis: {
                            categories: enrollmentCategories
                        },
                        theme: {
                            mode: document.documentElement.classList.contains('dark') ? 'dark' : 'light'
                        }
                    }).render();
                }

                // Fee Collection Chart
                const feeData = @json($chartData['feeData'] ?? []);
                if (feeData.length > 0 && document.querySelector("#fee-collection-chart")) {
                    const feeCategories = feeData.map(item => item.grade);
                    const feeCollected = feeData.map(item => parseFloat(item.collected));
                    const feeBalance = feeData.map(item => parseFloat(item.balance));

                    new ApexCharts(document.querySelector("#fee-collection-chart"), {
                        series: [{
                            name: 'Collected',
                            data: feeCollected
                        }, {
                            name: 'Balance',
                            data: feeBalance
                        }],
                        chart: {
                            type: 'bar',
                            height: 250,
                            stacked: true,
                            toolbar: {
                                show: false
                            }
                        },
                        plotOptions: {
                            bar: {
                                horizontal: false,
                                borderRadius: 4,
                                columnWidth: '55%',
                            },
                        },
                        dataLabels: {
                            enabled: false
                        },
                        colors: ['#10B981', '#F59E0B'],
                        xaxis: {
                            categories: feeCategories,
                        },
                        theme: {
                            mode: document.documentElement.classList.contains('dark') ? 'dark' : 'light'
                        }
                    }).render();
                }

                // Subject Performance Chart
                const resultData = @json($chartData['resultData'] ?? []);
                if (resultData.length > 0 && document.querySelector("#subject-performance-chart")) {
                    const subjectNames = resultData.map(item => item.name);
                    const subjectAverages = resultData.map(item => parseFloat(item.average).toFixed(1));

                    new ApexCharts(document.querySelector("#subject-performance-chart"), {
                        series: [{
                            name: 'Average Score',
                            data: subjectAverages
                        }],
                        chart: {
                            type: 'bar',
                            height: 250,
                            toolbar: {
                                show: false
                            }
                        },
                        plotOptions: {
                            bar: {
                                borderRadius: 4,
                                horizontal: true,
                            }
                        },
                        dataLabels: {
                            enabled: true,
                            formatter: function (val) {
                                return val + '%';
                            },
                            offsetX: 5
                        },
                        colors: ['#3B82F6'],
                        xaxis: {
                            categories: subjectNames,
                            labels: {
                                formatter: function (val) {
                                    return val + '%';
                                }
                            }
                        },
                        theme: {
                            mode: document.documentElement.classList.contains('dark') ? 'dark' : 'light'
                        }
                    }).render();
                }
            @endif

            // Activity Tabs Functionality
            const activityTabs = ['all', 'students', 'payments', 'submissions'];

            activityTabs.forEach(tab => {
                const tabButton = document.getElementById(`activity-tab-${tab}`);
                if (tabButton) {
                    tabButton.addEventListener('click', function() {
                        // Hide all content
                        activityTabs.forEach(t => {
                            const content = document.getElementById(`activity-${t}`);
                            const button = document.getElementById(`activity-tab-${t}`);
                            if (content) content.classList.add('hidden');
                            if (button) {
                                button.classList.remove('bg-primary-100', 'text-primary-700', 'dark:bg-primary-900', 'dark:text-primary-400');
                                button.classList.add('bg-gray-100', 'dark:bg-gray-700');
                            }
                        });

                        // Show selected content
                        const selectedContent = document.getElementById(`activity-${tab}`);
                        if (selectedContent) selectedContent.classList.remove('hidden');

                        // Update button styling
                        tabButton.classList.remove('bg-gray-100', 'dark:bg-gray-700');
                        tabButton.classList.add('bg-primary-100', 'text-primary-700', 'dark:bg-primary-900', 'dark:text-primary-400');
                    });
                }
            });

            // Initialize the first tab
            const firstTab = document.getElementById('activity-tab-all');
            if (firstTab) firstTab.click();
        });
    </script>
    @endpush
</x-filament-panels::page>
