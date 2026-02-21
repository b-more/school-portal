<x-filament-panels::page>
    @php
        $teacher = $this->getTeacher();
        $classes = $this->getMyClasses();
        $subjects = $this->getMySubjects();
        $academicYear = $this->getAcademicYear();
        $currentTerm = $this->getCurrentTerm();
        $totalStudents = $this->getTotalStudents();
        $homeworkStats = $this->getHomeworkStats();
    @endphp

    @if(!$teacher)
        <div class="text-center py-16">
            <x-heroicon-o-exclamation-triangle class="w-16 h-16 mx-auto text-warning-500 mb-4" />
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Teacher Profile Not Found</h3>
            <p class="text-gray-500 dark:text-gray-400 mt-2">Your user account is not linked to a teacher profile. Please contact the administrator.</p>
        </div>
    @else

    {{-- Academic Context Banner --}}
    <div class="mb-6 rounded-xl border border-gray-200 dark:border-gray-700 bg-gradient-to-r from-primary-50 to-primary-100 dark:from-gray-800 dark:to-gray-900 p-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-primary-600 text-white">
                    <x-heroicon-o-calendar class="w-5 h-5" />
                </div>
                <div>
                    <p class="text-sm font-medium text-primary-700 dark:text-primary-400">Academic Year</p>
                    <p class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $academicYear?->name ?? 'Not Set' }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-success-600 text-white">
                    <x-heroicon-o-clock class="w-5 h-5" />
                </div>
                <div>
                    <p class="text-sm font-medium text-success-700 dark:text-success-400">Current Term</p>
                    <p class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $currentTerm?->name ?? 'Not Set' }}</p>
                </div>
            </div>
            @if($currentTerm?->end_date)
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-warning-600 text-white">
                        <x-heroicon-o-arrow-trending-up class="w-5 h-5" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-warning-700 dark:text-warning-400">Term Ends</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $currentTerm->end_date->format('M d, Y') }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Teacher Profile Card --}}
    <div class="mb-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
        <div class="p-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5">
                {{-- Avatar --}}
                <div class="flex-shrink-0">
                    @if($teacher->profile_photo)
                        <img src="{{ asset('storage/' . $teacher->profile_photo) }}" alt="{{ $teacher->name }}" class="w-20 h-20 rounded-full object-cover ring-4 ring-primary-100 dark:ring-primary-900">
                    @else
                        <div class="w-20 h-20 rounded-full bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center text-white text-3xl font-bold ring-4 ring-primary-100 dark:ring-primary-900">
                            {{ strtoupper(substr($teacher->name, 0, 1)) }}
                        </div>
                    @endif
                </div>

                {{-- Teacher Details --}}
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $teacher->name }}</h2>
                        @if($teacher->is_class_teacher)
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-200">
                                <x-heroicon-m-star class="w-3 h-3" />
                                Class Teacher
                            </span>
                        @endif
                    </div>
                    <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm text-gray-600 dark:text-gray-400">
                        @if($teacher->employee_id)
                            <span class="flex items-center gap-1">
                                <x-heroicon-m-identification class="w-4 h-4" />
                                {{ $teacher->employee_id }}
                            </span>
                        @endif
                        @if($teacher->qualification)
                            <span class="flex items-center gap-1">
                                <x-heroicon-m-academic-cap class="w-4 h-4" />
                                {{ $teacher->qualification }}
                            </span>
                        @endif
                        @if($teacher->specialization)
                            <span class="flex items-center gap-1">
                                <x-heroicon-m-beaker class="w-4 h-4" />
                                {{ $teacher->specialization }}
                            </span>
                        @endif
                        @if($teacher->phone)
                            <span class="flex items-center gap-1">
                                <x-heroicon-m-phone class="w-4 h-4" />
                                {{ $teacher->phone }}
                            </span>
                        @endif
                    </div>
                    @if($teacher->is_class_teacher && $teacher->classSection)
                        <p class="mt-2 text-sm font-medium text-primary-600 dark:text-primary-400">
                            Class Teacher of {{ $teacher->classSection->grade->name ?? '' }} - {{ $teacher->classSection->name }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Overview --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        {{-- Classes --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-5">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center">
                    <x-heroicon-o-academic-cap class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $classes->count() }}</p>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Classes</p>
                </div>
            </div>
        </div>

        {{-- Subjects --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-5">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-900/50 flex items-center justify-center">
                    <x-heroicon-o-book-open class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $subjects->count() }}</p>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Subjects</p>
                </div>
            </div>
        </div>

        {{-- Students --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-5">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-900/50 flex items-center justify-center">
                    <x-heroicon-o-user-group class="w-6 h-6 text-amber-600 dark:text-amber-400" />
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $totalStudents }}</p>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Students</p>
                </div>
            </div>
        </div>

        {{-- Homework --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-5">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-green-100 dark:bg-green-900/50 flex items-center justify-center">
                    <x-heroicon-o-clipboard-document-list class="w-6 h-6 text-green-600 dark:text-green-400" />
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $homeworkStats['total'] }}</p>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Homework</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content: Two Column Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left Column: Classes (2/3 width) --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- My Classes --}}
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-academic-cap class="w-5 h-5 text-primary-600" />
                        <span>My Classes & Subjects</span>
                    </div>
                </x-slot>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @forelse($classes as $classSectionId => $teachings)
                        @php
                            $classSection = $teachings->first()->classSection;
                            $studentCount = $classSection ? $classSection->students->where('enrollment_status', 'active')->count() : 0;
                            $capacity = $classSection->capacity ?? 0;
                            $fillPercent = $capacity > 0 ? round(($studentCount / $capacity) * 100) : 0;
                        @endphp
                        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden hover:shadow-md transition-shadow">
                            {{-- Class Header --}}
                            <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">
                                        {{ $classSection->grade->name ?? 'N/A' }} - {{ $classSection->name }}
                                    </h3>
                                    @if($teacher->is_class_teacher && $teacher->class_section_id === $classSection->id)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-success-100 text-success-700 dark:bg-success-900 dark:text-success-300">
                                            <x-heroicon-m-star class="w-3 h-3" />
                                            CT
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="p-4">
                                {{-- Subjects List --}}
                                <div class="space-y-1.5 mb-4">
                                    @foreach($teachings as $teaching)
                                        <div class="flex items-center gap-2 text-sm">
                                            <span class="w-1.5 h-1.5 rounded-full bg-primary-500 flex-shrink-0"></span>
                                            <span class="text-gray-700 dark:text-gray-300">{{ $teaching->subject->name }}</span>
                                        </div>
                                    @endforeach
                                </div>

                                {{-- Student Count Bar --}}
                                <div class="pt-3 border-t border-gray-100 dark:border-gray-700">
                                    <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mb-1.5">
                                        <span>{{ $studentCount }} students enrolled</span>
                                        @if($capacity > 0)
                                            <span>{{ $fillPercent }}% capacity</span>
                                        @endif
                                    </div>
                                    @if($capacity > 0)
                                        <div class="w-full h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full transition-all {{ $fillPercent >= 90 ? 'bg-danger-500' : ($fillPercent >= 70 ? 'bg-warning-500' : 'bg-success-500') }}"
                                                 style="width: {{ min($fillPercent, 100) }}%"></div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full">
                            <div class="text-center py-12 bg-gray-50 dark:bg-gray-800 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600">
                                <x-heroicon-o-academic-cap class="w-12 h-12 mx-auto text-gray-400 mb-3" />
                                <p class="text-gray-500 dark:text-gray-400 font-medium">No classes assigned for this academic year</p>
                                <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Contact the administrator to get assigned to classes.</p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </x-filament::section>

            {{-- My Subjects --}}
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-book-open class="w-5 h-5 text-purple-600" />
                        <span>My Subjects</span>
                    </div>
                </x-slot>

                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    @forelse($subjects as $subject)
                        @php
                            $subjectClasses = $classes->filter(function($teachings) use ($subject) {
                                return $teachings->pluck('subject.id')->contains($subject->id);
                            });
                        @endphp
                        <div class="flex items-center gap-3 bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-lg p-3 hover:border-purple-300 dark:hover:border-purple-700 transition-colors">
                            <div class="flex-shrink-0 w-9 h-9 rounded-lg bg-purple-100 dark:bg-purple-900/50 flex items-center justify-center">
                                <x-heroicon-o-book-open class="w-4 h-4 text-purple-600 dark:text-purple-400" />
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $subject->name }}</h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $subjectClasses->count() }} {{ Str::plural('class', $subjectClasses->count()) }}</p>
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
        </div>

        {{-- Right Column: Sidebar --}}
        <div class="space-y-6">

            {{-- Quick Actions --}}
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-bolt class="w-5 h-5 text-warning-600" />
                        <span>Quick Actions</span>
                    </div>
                </x-slot>

                <div class="space-y-2">
                    <a href="{{ url('/admin/homework/create') }}"
                       class="flex items-center gap-3 w-full p-3 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-900/50 hover:bg-primary-50 hover:text-primary-700 dark:hover:bg-primary-900/20 dark:hover:text-primary-400 border border-gray-200 dark:border-gray-700 transition-colors">
                        <x-heroicon-o-plus-circle class="w-5 h-5 flex-shrink-0" />
                        <span>Assign Homework</span>
                    </a>
                    <a href="{{ url('/admin/homework') }}"
                       class="flex items-center gap-3 w-full p-3 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-900/50 hover:bg-primary-50 hover:text-primary-700 dark:hover:bg-primary-900/20 dark:hover:text-primary-400 border border-gray-200 dark:border-gray-700 transition-colors">
                        <x-heroicon-o-clipboard-document-list class="w-5 h-5 flex-shrink-0" />
                        <span>View All Homework</span>
                    </a>
                    <a href="{{ url('/admin/my-teaching-documents') }}"
                       class="flex items-center gap-3 w-full p-3 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-900/50 hover:bg-primary-50 hover:text-primary-700 dark:hover:bg-primary-900/20 dark:hover:text-primary-400 border border-gray-200 dark:border-gray-700 transition-colors">
                        <x-heroicon-o-document-arrow-up class="w-5 h-5 flex-shrink-0" />
                        <span>My Documents</span>
                    </a>
                </div>
            </x-filament::section>

            {{-- Homework Summary --}}
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-clipboard-document-list class="w-5 h-5 text-green-600" />
                        <span>Homework Summary</span>
                    </div>
                </x-slot>

                <div class="space-y-3">
                    <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Total Assigned</span>
                        <span class="text-sm font-bold text-gray-900 dark:text-gray-100">{{ $homeworkStats['total'] }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Active</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-success-100 text-success-700 dark:bg-success-900 dark:text-success-300">{{ $homeworkStats['active'] }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Past Due</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">{{ $homeworkStats['past_due'] }}</span>
                    </div>
                </div>
            </x-filament::section>

            {{-- Recent Homework --}}
            @if($homeworkStats['recent']->isNotEmpty())
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-clock class="w-5 h-5 text-blue-600" />
                        <span>Recent Homework</span>
                    </div>
                </x-slot>

                <div class="space-y-3">
                    @foreach($homeworkStats['recent'] as $hw)
                        <div class="flex items-start gap-3 py-2 {{ !$loop->last ? 'border-b border-gray-100 dark:border-gray-700' : '' }}">
                            <div class="flex-shrink-0 mt-0.5">
                                @if($hw->due_date && $hw->due_date->isPast())
                                    <div class="w-2 h-2 rounded-full bg-gray-400"></div>
                                @else
                                    <div class="w-2 h-2 rounded-full bg-success-500"></div>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ $hw->title }}</p>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $hw->subject?->name ?? 'N/A' }}</span>
                                    @if($hw->due_date)
                                        <span class="text-xs text-gray-400 dark:text-gray-500">{{ $hw->due_date->format('M d') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-filament::section>
            @endif

            {{-- Teaching Load Summary --}}
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-chart-bar class="w-5 h-5 text-indigo-600" />
                        <span>Teaching Load</span>
                    </div>
                </x-slot>

                <div class="space-y-4">
                    @php
                        $totalTeachingSlots = $classes->sum(fn($teachings) => $teachings->count());
                    @endphp

                    <div class="text-center py-3">
                        <p class="text-4xl font-bold text-gray-900 dark:text-gray-100">{{ $totalTeachingSlots }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Teaching Assignments</p>
                    </div>

                    <div class="pt-3 border-t border-gray-100 dark:border-gray-700">
                        <div class="grid grid-cols-2 gap-3 text-center">
                            <div>
                                <p class="text-xl font-bold text-blue-600 dark:text-blue-400">{{ $subjects->count() }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Subjects</p>
                            </div>
                            <div>
                                <p class="text-xl font-bold text-amber-600 dark:text-amber-400">{{ $classes->count() }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Sections</p>
                            </div>
                        </div>
                    </div>

                    @if($classes->isNotEmpty())
                    <div class="pt-3 border-t border-gray-100 dark:border-gray-700">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-wide">Per Class Breakdown</p>
                        @foreach($classes as $classSectionId => $teachings)
                            @php $cs = $teachings->first()->classSection; @endphp
                            <div class="flex items-center justify-between py-1.5 text-sm">
                                <span class="text-gray-700 dark:text-gray-300">{{ $cs->grade->name ?? '' }} {{ $cs->name }}</span>
                                <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $teachings->count() }} {{ Str::plural('subject', $teachings->count()) }}</span>
                            </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </x-filament::section>
        </div>
    </div>

    @endif
</x-filament-panels::page>
