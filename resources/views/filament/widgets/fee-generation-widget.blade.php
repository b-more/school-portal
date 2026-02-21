<x-filament-widgets::widget>
    <x-filament::section>
        @if (!$hasActiveTerm)
            <div class="bg-gradient-to-r from-red-500 to-red-600 rounded-lg p-6 text-white shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-xl font-semibold">Fee Management</h3>
                        <p class="text-red-100 text-sm">No active term found</p>
                    </div>
                    <svg class="w-12 h-12 text-red-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <div class="bg-white/20 backdrop-blur-sm rounded-md p-4">
                    <p class="text-sm">{{ $message }}</p>
                </div>
            </div>
        @else
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-6 text-white shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-xl font-semibold">Fee Management</h3>
                        <p class="text-blue-100 text-sm">{{ $currentTerm }} - {{ $currentAcademicYear }}</p>
                    </div>
                    <svg class="w-12 h-12 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                </div>

                <!-- Overall Statistics -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white/20 backdrop-blur-sm rounded-md p-3 text-center">
                        <div class="text-2xl font-bold">{{ $stats['total_students'] }}</div>
                        <div class="text-xs text-blue-100">Total Students</div>
                    </div>
                    <div class="bg-white/20 backdrop-blur-sm rounded-md p-3 text-center">
                        <div class="text-2xl font-bold text-green-200">{{ $stats['students_with_fees'] }}</div>
                        <div class="text-xs text-blue-100">With Fees</div>
                    </div>
                    <div class="bg-white/20 backdrop-blur-sm rounded-md p-3 text-center">
                        <div class="text-2xl font-bold text-yellow-200">{{ $stats['students_needing_fees'] }}</div>
                        <div class="text-xs text-blue-100">Need Fees</div>
                    </div>
                    <div class="bg-white/20 backdrop-blur-sm rounded-md p-3 text-center">
                        <div class="text-2xl font-bold">{{ $stats['completion_percentage'] }}%</div>
                        <div class="text-xs text-blue-100">Complete</div>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="mb-6">
                    <div class="flex justify-between text-sm mb-2">
                        <span>Fee Assignment Progress</span>
                        <span>{{ $stats['completion_percentage'] }}% Complete</span>
                    </div>
                    <div class="w-full bg-white/20 rounded-full h-3">
                        <div class="bg-green-300 h-3 rounded-full transition-all duration-500"
                             style="width: {{ $stats['completion_percentage'] }}%"></div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="flex flex-wrap gap-3 mb-6">
                    {{ ($this->previewFeeGenerationAction)() }}
                    {{ ($this->generateFeesAction)() }}

                    <a href="/admin/student-fees"
                       class="bg-white/20 hover:bg-white/30 backdrop-blur-sm rounded-md px-4 py-2 transition-all duration-200 inline-flex items-center text-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        Manage All Fees
                    </a>
                </div>

                <!-- Grade Breakdown -->
                @if (!empty($gradeBreakdown))
                    <div class="bg-white/10 backdrop-blur-sm rounded-md p-4">
                        <h4 class="font-semibold mb-3 text-sm">Breakdown by Grade</h4>
                        <div class="space-y-2">
                            @foreach ($gradeBreakdown as $grade)
                                <div class="flex items-center justify-between text-sm">
                                    <div class="flex-1">
                                        <span class="font-medium">{{ $grade['name'] }}</span>
                                        <span class="text-blue-100 ml-2">
                                            ({{ $grade['with_fees'] }}/{{ $grade['total'] }})
                                        </span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        @if ($grade['need_fees'] > 0)
                                            <span class="bg-yellow-500/30 text-yellow-100 px-2 py-1 rounded text-xs">
                                                {{ $grade['need_fees'] }} missing
                                            </span>
                                        @else
                                            <span class="bg-green-500/30 text-green-100 px-2 py-1 rounded text-xs">
                                                Complete
                                            </span>
                                        @endif
                                        <span class="text-xs">{{ $grade['percentage'] }}%</span>
                                    </div>
                                </div>
                                <div class="w-full bg-white/20 rounded-full h-1">
                                    <div class="bg-white h-1 rounded-full transition-all duration-300"
                                         style="width: {{ $grade['percentage'] }}%"></div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Help Text -->
                <div class="mt-4 text-xs text-blue-100">
                    <p><strong>Tip:</strong> Use "Preview" to see how many fees will be created before running the generation.</p>
                    <p><strong>Note:</strong> Fees are only created for students who don't already have fees for the current term.</p>
                </div>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
