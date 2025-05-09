<x-filament::page>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-3">
            <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
                <h2 class="text-lg font-bold mb-4">School Overview</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
                    @foreach($stats as $stat)
                        <div class="bg-white rounded-xl border border-gray-200 p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-500">{{ $stat->getLabel() }}</p>
                                    <p class="text-2xl font-bold">{{ $stat->getValue() }}</p>
                                </div>
                                @if($stat->getIcon())
                                    <div class="w-10 h-10 rounded-full bg-{{ $stat->getColor() }}-100 flex items-center justify-center">
                                        <x-dynamic-component :component="$stat->getIcon()" class="w-5 h-5 text-{{ $stat->getColor() }}-500" />
                                    </div>
                                @endif
                            </div>
                            @if($stat->getDescription())
                                <div class="mt-2 flex items-center text-sm text-gray-500">
                                    @if($stat->getDescriptionIcon())
                                        <x-dynamic-component :component="$stat->getDescriptionIcon()" class="w-4 h-4 mr-1" />
                                    @endif
                                    <span>{{ $stat->getDescription() }}</span>
                                </div>
                            @endif
                            @if($stat->getChart())
                                <div class="mt-2">
                                    <div class="flex items-end justify-between h-10">
                                        @foreach($stat->getChart() as $chartItem)
                                            <div class="w-full">
                                                <div class="bg-{{ $stat->getColor() }}-500 rounded-t" style="height: {{ min(100, max(15, $chartItem / max($stat->getChart()) * 100)) }}%;"></div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="lg:col-span-2 space-y-4">
            <!-- Chart Section -->
            <div class="bg-white rounded-xl shadow-sm p-4">
                <h2 class="text-lg font-bold mb-4">Student Enrollment by Grade</h2>
                <div id="enrollment-chart" class="h-64"></div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-4">
                <h2 class="text-lg font-bold mb-4">Fee Collection Status</h2>
                <div id="fee-collection-chart" class="h-64"></div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-4">
                <h2 class="text-lg font-bold mb-4">Subject Performance</h2>
                <div id="subject-performance-chart" class="h-64"></div>
            </div>

            <!-- Recent Activity Section -->
            <div class="bg-white rounded-xl shadow-sm p-4">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-bold">Recent Activity</h2>
                    <div class="flex space-x-2">
                        <button class="px-3 py-1 text-sm bg-gray-100 rounded-full" id="activity-tab-students">Students</button>
                        <button class="px-3 py-1 text-sm bg-gray-100 rounded-full" id="activity-tab-payments">Payments</button>
                        <button class="px-3 py-1 text-sm bg-gray-100 rounded-full" id="activity-tab-homework">Homework</button>
                        <button class="px-3 py-1 text-sm bg-gray-100 rounded-full" id="activity-tab-sms">SMS</button>
                    </div>
                </div>

                <div id="activity-students" class="activity-content">
                    <div class="space-y-3">
                        @forelse($recentActivity['students'] as $student)
                            <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                <div class="flex-shrink-0 mr-3">
                                    @if(isset($student->profile_photo) && $student->profile_photo)
                                        <img src="{{ Storage::url($student->profile_photo) }}" alt="{{ $student->name }}" class="w-10 h-10 rounded-full">
                                    @else
                                        <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
                                            <span class="text-gray-600 font-bold">{{ substr($student->name ?? 'S', 0, 1) }}</span>
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <p class="font-medium">{{ $student->name ?? 'Unknown' }}</p>
                                    <p class="text-sm text-gray-500">{{ $student->grade ?? 'N/A' }} | {{ isset($student->created_at) ? $student->created_at->diffForHumans() : 'N/A' }}</p>
                                </div>
                                <div class="ml-auto">
                                    @if(Route::has('filament.admin.resources.students.edit'))
                                        <a href="{{ route('filament.admin.resources.students.edit', $student) }}" class="text-primary-500 hover:underline text-sm">View</a>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500 text-center py-4">No recent students</p>
                        @endforelse
                    </div>
                </div>

                <div id="activity-payments" class="activity-content hidden">
                    <div class="space-y-3">
                        @forelse($recentActivity['payments'] as $payment)
                            <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                <div class="flex-shrink-0 mr-3">
                                    <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                                        <x-heroicon-o-banknotes class="w-5 h-5 text-green-500" />
                                    </div>
                                </div>
                                <div>
                                    <p class="font-medium">{{ $payment->student->name ?? 'Unknown Student' }}</p>
                                    <p class="text-sm text-gray-500">
                                        ZMW {{ number_format($payment->amount_paid ?? 0, 2) }} |
                                        {{ isset($payment->payment_date) ? $payment->payment_date->format('M d, Y') : 'No date' }}
                                    </p>
                                </div>
                                <div class="ml-auto">
                                    @if(Route::has('filament.admin.resources.student-fees.edit'))
                                        <a href="{{ route('filament.admin.resources.student-fees.edit', $payment) }}" class="text-primary-500 hover:underline text-sm">View</a>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500 text-center py-4">No recent payments</p>
                        @endforelse
                    </div>
                </div>

                <div id="activity-homework" class="activity-content hidden">
                    <div class="space-y-3">
                        @forelse($recentActivity['submissions'] as $submission)
                            <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                <div class="flex-shrink-0 mr-3">
                                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                        <x-heroicon-o-document-text class="w-5 h-5 text-blue-500" />
                                    </div>
                                </div>
                                <div>
                                    <p class="font-medium">{{ $submission->student->name ?? 'Unknown Student' }}</p>
                                    <p class="text-sm text-gray-500">
                                        {{ $submission->homework->title ?? 'Unknown Homework' }} |
                                        {{ isset($submission->created_at) ? $submission->created_at->diffForHumans() : 'N/A' }}
                                    </p>
                                </div>
                                <div class="ml-auto">
                                    @if(Route::has('filament.admin.resources.homework-submissions.edit'))
                                        <a href="{{ route('filament.admin.resources.homework-submissions.edit', $submission) }}" class="text-primary-500 hover:underline text-sm">View</a>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500 text-center py-4">No recent submissions</p>
                        @endforelse
                    </div>
                </div>

                <div id="activity-sms" class="activity-content hidden">
                    <div class="space-y-3">
                        @forelse($recentActivity['sms'] as $sms)
                            <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                <div class="flex-shrink-0 mr-3">
                                    <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center">
                                        <x-heroicon-o-chat-bubble-left class="w-5 h-5 text-yellow-500" />
                                    </div>
                                </div>
                                <div>
                                    <p class="font-medium">{{ isset($sms->recipient) ? substr($sms->recipient, 0, 6) . '****' . substr($sms->recipient, -3) : 'Unknown' }}</p>
                                    <p class="text-sm text-gray-500">
                                        {{ $sms->message_type ?? 'N/A' }} |
                                        {{ isset($sms->created_at) ? $sms->created_at->diffForHumans() : 'N/A' }}
                                    </p>
                                </div>
                                <div class="ml-auto">
                                    <span class="px-2 py-1 text-xs rounded-full {{ ($sms->status ?? '') === 'sent' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ ucfirst($sms->status ?? 'Unknown') }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500 text-center py-4">No recent SMS messages</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-1 space-y-4">
            <!-- Quick Actions -->
            <div class="bg-white rounded-xl shadow-sm p-4">
                <h2 class="text-lg font-bold mb-4">Quick Actions</h2>
                <div class="grid grid-cols-2 gap-3">
                    @foreach($quickActions as $action)
                        <a href="{{ $action['url'] }}" class="flex flex-col items-center justify-center p-4 bg-{{ $action['color'] }}-50 rounded-lg hover:bg-{{ $action['color'] }}-100 transition-colors">
                            <div class="w-10 h-10 rounded-full bg-{{ $action['color'] }}-100 flex items-center justify-center mb-2">
                                <x-dynamic-component :component="$action['icon']" class="w-5 h-5 text-{{ $action['color'] }}-500" />
                            </div>
                            <span class="text-sm font-medium text-center">{{ $action['title'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Upcoming Events -->
            <div class="bg-white rounded-xl shadow-sm p-4">
                <h2 class="text-lg font-bold mb-4">Upcoming Events</h2>
                <div class="space-y-3">
                    @forelse($upcomingEvents as $event)
                        <div class="p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 mr-3">
                                    <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                        <x-heroicon-o-calendar class="w-5 h-5 text-indigo-500" />
                                    </div>
                                </div>
                                <div>
                                    <p class="font-medium">{{ $event['title'] ?? 'Untitled Event' }}</p>
                                    <p class="text-sm text-gray-500">
                                        {{ isset($event['start_date']) ? \Carbon\Carbon::parse($event['start_date'])->format('M d, Y') : 'No date' }}
                                        @if(isset($event['location']) && $event['location'])
                                            | {{ $event['location'] }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500 text-center py-4">No upcoming events</p>
                    @endforelse
                </div>
                <div class="mt-3">
                    @if(Route::has('filament.admin.resources.events.index'))
                        <a href="{{ route('filament.admin.resources.events.index') }}" class="text-primary-500 hover:underline text-sm">View all events</a>
                    @endif
                </div>
            </div>

            <!-- System Status -->
            <div class="bg-white rounded-xl shadow-sm p-4">
                <h2 class="text-lg font-bold mb-4">System Status</h2>
                <div class="space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-sm">SMS Balance</span>
                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Active</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm">Database</span>
                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Connected</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm">Storage</span>
                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">88% Free</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm">Last Backup</span>
                        <span class="text-sm">{{ now()->subDay()->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Enrollment Chart
            const enrollmentData = @json($chartData['gradeData'] ?? []);
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
                }
            }).render();

            // Fee Collection Chart
            const feeData = @json($chartData['feeData'] ?? []);
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
                }
            }).render();

            // Subject Performance Chart
            const resultData = @json($chartData['resultData'] ?? []);
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
                }
            }).render();

            // Activity Tabs
            const activityTabs = ['students', 'payments', 'homework', 'sms'];

            activityTabs.forEach(tab => {
                document.getElementById(`activity-tab-${tab}`).addEventListener('click', function() {
                    // Hide all content
                    activityTabs.forEach(t => {
                        document.getElementById(`activity-${t}`).classList.add('hidden');
                        document.getElementById(`activity-tab-${t}`).classList.remove('bg-primary-100', 'text-primary-700');
                        document.getElementById(`activity-tab-${t}`).classList.add('bg-gray-100');
                    });

                    // Show selected content
                    document.getElementById(`activity-${tab}`).classList.remove('hidden');
                    document.getElementById(`activity-tab-${tab}`).classList.remove('bg-gray-100');
                    document.getElementById(`activity-tab-${tab}`).classList.add('bg-primary-100', 'text-primary-700');
                });
            });

            // Initialize the first tab
            document.getElementById('activity-tab-students').click();
        });
    </script>
    @endpush
</x-filament::page>
