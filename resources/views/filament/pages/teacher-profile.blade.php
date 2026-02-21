<x-filament-panels::page>
    @php
        $teacher = $this->getTeacher();
    @endphp

    @if($teacher)
        <!-- Enhanced Profile Header with Neutral Colors -->
        <div class="mb-6 overflow-hidden bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-xl shadow-lg">
            <div class="p-6">
                <div class="flex flex-col md:flex-row items-center md:items-start gap-6">
                    <!-- Profile Photo -->
                    <div class="flex-shrink-0">
                        @if($teacher->profile_photo)
                            <img src="{{ Storage::url($teacher->profile_photo) }}"
                                 alt="{{ $teacher->name }}"
                                 class="w-32 h-32 rounded-xl border-4 border-gray-300 dark:border-gray-600 shadow-lg object-cover">
                        @else
                            <div class="w-32 h-32 rounded-xl border-4 border-gray-300 dark:border-gray-600 shadow-lg bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800 flex items-center justify-center">
                                <span class="text-5xl font-bold text-gray-700 dark:text-gray-300">{{ strtoupper(substr($teacher->name, 0, 1)) }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- Profile Info -->
                    <div class="flex-1 text-center md:text-left">
                        <h2 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-2">{{ $teacher->name }}</h2>
                        <p class="text-gray-600 dark:text-gray-400 text-lg mb-4 font-medium">{{ $teacher->employee_id }}</p>

                        <div class="flex flex-wrap gap-2 justify-center md:justify-start mb-4">
                            @if($teacher->is_class_teacher)
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-semibold bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 border border-green-300 dark:border-green-700">
                                    <x-heroicon-o-user-group class="w-4 h-4 mr-1.5" />
                                    Class Teacher
                                </span>
                            @endif
                            @if($teacher->is_grade_teacher)
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-semibold bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200 border border-purple-300 dark:border-purple-700">
                                    <x-heroicon-o-academic-cap class="w-4 h-4 mr-1.5" />
                                    Grade Teacher
                                </span>
                            @endif
                            @if($teacher->is_active)
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 border border-blue-300 dark:border-blue-700">
                                    <x-heroicon-o-check-circle class="w-4 h-4 mr-1.5" />
                                    Active
                                </span>
                            @endif
                        </div>

                        <!-- Quick Stats -->
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <div class="bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-3.5 shadow-sm">
                                <div class="text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wide font-semibold mb-1">Qualification</div>
                                <div class="text-gray-900 dark:text-gray-100 font-bold text-lg">{{ $teacher->qualification }}</div>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-3.5 shadow-sm">
                                <div class="text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wide font-semibold mb-1">Joined</div>
                                <div class="text-gray-900 dark:text-gray-100 font-bold text-lg">{{ $teacher->join_date ? $teacher->join_date->format('M Y') : 'N/A' }}</div>
                            </div>
                            @if($teacher->specialization)
                            <div class="bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-3.5 shadow-sm col-span-2 md:col-span-1">
                                <div class="text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wide font-semibold mb-1">Specialization</div>
                                <div class="text-gray-900 dark:text-gray-100 font-bold text-lg">{{ $teacher->specialization }}</div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <form wire:submit="updateProfile">
        {{ $this->form }}

        <div class="mt-6 flex justify-end">
            <x-filament::button type="submit" size="lg">
                <x-heroicon-o-check-circle class="w-5 h-5 mr-2" />
                Update Profile
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
