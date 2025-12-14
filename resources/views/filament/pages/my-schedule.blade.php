<x-filament-panels::page>
    @php
        $teacher = $this->getTeacher();
        $classes = $this->getMyClasses();
        $subjects = $this->getMySubjects();
    @endphp

    <!-- Teacher Info Summary -->
    <div class="mb-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
        <div class="flex items-center gap-4">
            <div class="flex-shrink-0">
                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white text-2xl font-bold">
                    {{ $teacher ? strtoupper(substr($teacher->name, 0, 1)) : 'T' }}
                </div>
            </div>
            <div class="flex-1">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $teacher?->name ?? 'Teacher' }}</h2>
                <p class="text-gray-600 dark:text-gray-400">{{ $teacher?->employee_id ?? 'N/A' }} | {{ $teacher?->qualification ?? 'N/A' }}</p>
            </div>
            <div class="text-right">
                <div class="text-sm text-gray-500 dark:text-gray-400">Total Classes</div>
                <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $classes->count() }}</div>
            </div>
        </div>
    </div>

    <!-- My Classes Section -->
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-heroicon-o-academic-cap class="w-6 h-6 text-blue-600" />
                <span>My Classes</span>
            </div>
        </x-slot>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($classes as $classSectionId => $teachings)
                @php
                    $classSection = $teachings->first()->classSection;
                @endphp
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-800 border-2 border-blue-200 dark:border-blue-700 rounded-lg p-4 hover:shadow-lg transition-shadow">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                {{ $classSection->grade->name ?? 'N/A' }} - {{ $classSection->name }}
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Section {{ $classSection->name }}</p>
                        </div>
                        @if($teacher?->is_class_teacher && $teacher->class_section_id === $classSection->id)
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                Class Teacher
                            </span>
                        @endif
                    </div>

                    <div class="space-y-2">
                        <div class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Subjects I Teach:</div>
                        @foreach($teachings as $teaching)
                            <div class="flex items-center gap-2 text-sm">
                                <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                                <span class="text-gray-900 dark:text-gray-100 font-medium">{{ $teaching->subject->name }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 pt-4 border-t border-blue-200 dark:border-gray-600">
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            <strong>Capacity:</strong> {{ $classSection->capacity }} students
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full">
                    <div class="text-center py-12 bg-gray-50 dark:bg-gray-800 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600">
                        <x-heroicon-o-academic-cap class="w-12 h-12 mx-auto text-gray-400 mb-3" />
                        <p class="text-gray-500 dark:text-gray-400">No classes assigned yet</p>
                    </div>
                </div>
            @endforelse
        </div>
    </x-filament::section>

    <!-- My Subjects Section -->
    <x-filament::section class="mt-6">
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-heroicon-o-book-open class="w-6 h-6 text-purple-600" />
                <span>My Subjects</span>
            </div>
        </x-slot>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @forelse($subjects as $subject)
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:border-purple-300 dark:hover:border-purple-700 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-900 flex items-center justify-center">
                            <x-heroicon-o-book-open class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 dark:text-gray-100">{{ $subject->name }}</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $subject->code ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full">
                    <div class="text-center py-8 bg-gray-50 dark:bg-gray-800 rounded-lg border border-dashed border-gray-300 dark:border-gray-600">
                        <p class="text-gray-500 dark:text-gray-400">No subjects assigned yet</p>
                    </div>
                </div>
            @endforelse
        </div>
    </x-filament::section>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
        <x-filament::section>
            <div class="text-center">
                <x-heroicon-o-user-group class="w-8 h-8 mx-auto text-blue-600 mb-2" />
                <div class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $classes->count() }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Classes Teaching</div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-center">
                <x-heroicon-o-book-open class="w-8 h-8 mx-auto text-purple-600 mb-2" />
                <div class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $subjects->count() }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Subjects</div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
