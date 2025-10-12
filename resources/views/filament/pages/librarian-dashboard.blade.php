<x-filament-panels::page>
    <!-- Library Statistics Grid -->
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
        @php
            $libraryStats = $this->getLibraryStats();
            $loanStats = $this->getLoanStats();
        @endphp

        <!-- Total Books -->
        <x-filament::section>
            <div class="p-4 text-center bg-blue-50 rounded-lg dark:bg-blue-900/20">
                <div class="p-3 mx-auto w-fit rounded-lg bg-blue-100 dark:bg-blue-800">
                    <x-heroicon-o-book-open class="w-8 h-8 text-blue-600 dark:text-blue-400" />
                </div>
                <div class="mt-4 text-3xl font-bold text-blue-700 dark:text-blue-300">
                    {{ $libraryStats['total_books'] }}
                </div>
                <div class="mt-1 text-sm text-blue-600 dark:text-blue-400">Total Books</div>
                <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    {{ $libraryStats['unique_titles'] }} unique titles
                </div>
            </div>
        </x-filament::section>

        <!-- Available Books -->
        <x-filament::section>
            <div class="p-4 text-center bg-green-50 rounded-lg dark:bg-green-900/20">
                <div class="p-3 mx-auto w-fit rounded-lg bg-green-100 dark:bg-green-800">
                    <x-heroicon-o-check-circle class="w-8 h-8 text-green-600 dark:text-green-400" />
                </div>
                <div class="mt-4 text-3xl font-bold text-green-700 dark:text-green-300">
                    {{ $libraryStats['available_copies'] }}
                </div>
                <div class="mt-1 text-sm text-green-600 dark:text-green-400">Available Copies</div>
            </div>
        </x-filament::section>

        <!-- Active Loans -->
        <x-filament::section>
            <div class="p-4 text-center bg-yellow-50 rounded-lg dark:bg-yellow-900/20">
                <div class="p-3 mx-auto w-fit rounded-lg bg-yellow-100 dark:bg-yellow-800">
                    <x-heroicon-o-arrow-right-circle class="w-8 h-8 text-yellow-600 dark:text-yellow-400" />
                </div>
                <div class="mt-4 text-3xl font-bold text-yellow-700 dark:text-yellow-300">
                    {{ $loanStats['active_loans'] }}
                </div>
                <div class="mt-1 text-sm text-yellow-600 dark:text-yellow-400">Active Loans</div>
                <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    {{ $loanStats['loans_this_month'] }} loans this month
                </div>
            </div>
        </x-filament::section>

        <!-- Overdue Books -->
        <x-filament::section>
            <div class="p-4 text-center bg-red-50 rounded-lg dark:bg-red-900/20">
                <div class="p-3 mx-auto w-fit rounded-lg bg-red-100 dark:bg-red-800">
                    <x-heroicon-o-exclamation-circle class="w-8 h-8 text-red-600 dark:text-red-400" />
                </div>
                <div class="mt-4 text-3xl font-bold text-red-700 dark:text-red-300">
                    {{ $loanStats['overdue_loans'] }}
                </div>
                <div class="mt-1 text-sm text-red-600 dark:text-red-400">Overdue Loans</div>
            </div>
        </x-filament::section>
    </div>

    <!-- Two Column Layout -->
    <div class="grid grid-cols-1 gap-6 mt-6 lg:grid-cols-2">
        <!-- Overdue Loans Section -->
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center">
                    <x-heroicon-o-exclamation-triangle class="w-5 h-5 mr-2 text-red-500" />
                    Overdue Loans
                </div>
            </x-slot>

            <div class="space-y-3">
                @forelse($this->getOverdueLoans() as $loan)
                    <div class="flex items-start p-4 bg-white border-l-4 border-red-500 rounded-lg shadow-sm dark:bg-gray-800">
                        <div class="flex-1">
                            <h4 class="font-medium text-gray-900 dark:text-white">{{ $loan->student->name }}</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $loan->book->title }}</p>
                            <div class="mt-2 flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                                <span>{{ $loan->student->grade?->name }}</span>
                                <span>•</span>
                                <span>{{ $loan->student->classSection?->name }}</span>
                                <span>•</span>
                                <span class="text-red-600 dark:text-red-400 font-medium">
                                    {{ $loan->daysOverdue() }} days overdue
                                </span>
                            </div>
                        </div>
                        <div class="ml-4 text-right">
                            <div class="text-xs text-gray-500 dark:text-gray-400">Due Date</div>
                            <div class="font-medium text-red-600 dark:text-red-400">
                                {{ $loan->due_date->format('M d, Y') }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
                        <p class="text-center text-gray-500 dark:text-gray-400">No overdue loans</p>
                    </div>
                @endforelse

                @if($this->getOverdueLoans()->count() > 0)
                    <div class="text-right">
                        <a href="{{ route('filament.admin.resources.book-loans.index', ['tableFilters[status][value]' => 'overdue']) }}"
                           class="text-sm text-primary-600 hover:underline">
                            View All Overdue Loans →
                        </a>
                    </div>
                @endif
            </div>
        </x-filament::section>

        <!-- Low Stock Books Section -->
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center">
                    <x-heroicon-o-arrow-trending-down class="w-5 h-5 mr-2 text-orange-500" />
                    Low Stock Books
                </div>
            </x-slot>

            <div class="space-y-3">
                @forelse($this->getLowStockBooks() as $book)
                    <div class="flex items-center p-4 bg-white rounded-lg shadow-sm dark:bg-gray-800">
                        <div class="flex-1">
                            <h4 class="font-medium text-gray-900 dark:text-white">{{ $book->title }}</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $book->author }}</p>
                            <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                <span class="inline-flex items-center">
                                    ISBN: {{ $book->isbn }}
                                </span>
                            </div>
                        </div>
                        <div class="ml-4 text-right">
                            <div class="text-xs text-gray-500 dark:text-gray-400">Available</div>
                            <div class="text-2xl font-bold {{ $book->available_copies == 0 ? 'text-red-600 dark:text-red-400' : 'text-orange-600 dark:text-orange-400' }}">
                                {{ $book->available_copies }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">of {{ $book->total_copies }}</div>
                        </div>
                    </div>
                @empty
                    <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
                        <p class="text-center text-gray-500 dark:text-gray-400">All books are well stocked</p>
                    </div>
                @endforelse
            </div>
        </x-filament::section>
    </div>

    <!-- Recent Activity -->
    <div class="grid grid-cols-1 gap-6 mt-6 lg:grid-cols-2">
        <!-- Recent Loans -->
        <x-filament::section>
            <x-slot name="heading">
                Recent Loans
            </x-slot>

            <div class="space-y-3">
                @forelse($this->getRecentLoans() as $loan)
                    <div class="flex items-center p-4 bg-white rounded-lg shadow-sm dark:bg-gray-800">
                        <div class="flex-1">
                            <h4 class="font-medium text-gray-900 dark:text-white">{{ $loan->student->name }}</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $loan->book->title }}</p>
                            <div class="mt-2 flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                                <span>{{ $loan->lent_at->format('M d, Y') }}</span>
                                <span>•</span>
                                <span>Due: {{ $loan->due_date->format('M d') }}</span>
                                @if($loan->lentBy)
                                    <span>•</span>
                                    <span>By: {{ $loan->lentBy->name }}</span>
                                @endif
                            </div>
                        </div>
                        <div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $loan->status === 'active' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' :
                                   ($loan->status === 'overdue' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' :
                                   'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300') }}">
                                {{ ucfirst($loan->status) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
                        <p class="text-center text-gray-500 dark:text-gray-400">No recent loans</p>
                    </div>
                @endforelse
            </div>
        </x-filament::section>

        <!-- Recent Returns -->
        <x-filament::section>
            <x-slot name="heading">
                Recent Returns
            </x-slot>

            <div class="space-y-3">
                @forelse($this->getRecentReturns() as $loan)
                    <div class="flex items-center p-4 bg-white rounded-lg shadow-sm dark:bg-gray-800">
                        <div class="flex-1">
                            <h4 class="font-medium text-gray-900 dark:text-white">{{ $loan->student->name }}</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $loan->book->title }}</p>
                            <div class="mt-2 flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                                <span>Returned: {{ $loan->returned_at->format('M d, Y') }}</span>
                                @if($loan->condition_on_return)
                                    <span>•</span>
                                    <span>Condition: {{ ucfirst($loan->condition_on_return) }}</span>
                                @endif
                                @if($loan->fine_amount > 0)
                                    <span>•</span>
                                    <span class="text-red-600 dark:text-red-400 font-medium">
                                        Fine: ZMW {{ number_format($loan->fine_amount, 2) }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                Returned
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
                        <p class="text-center text-gray-500 dark:text-gray-400">No recent returns</p>
                    </div>
                @endforelse
            </div>
        </x-filament::section>
    </div>

    <!-- Popular Books This Month -->
    <x-filament::section class="mt-6">
        <x-slot name="heading">
            <div class="flex items-center">
                <x-heroicon-o-fire class="w-5 h-5 mr-2 text-orange-500" />
                Popular Books This Month
            </div>
        </x-slot>

        <div class="space-y-3">
            @forelse($this->getPopularBooks() as $book)
                <div class="flex items-center p-4 bg-white rounded-lg shadow-sm dark:bg-gray-800">
                    <div class="flex-1">
                        <h4 class="font-medium text-gray-900 dark:text-white">{{ $book->title }}</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $book->author }}</p>
                        <div class="mt-2 flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                            <span>{{ $book->category }}</span>
                            <span>•</span>
                            <span>ISBN: {{ $book->isbn }}</span>
                            <span>•</span>
                            <span>{{ $book->available_copies }}/{{ $book->total_copies }} available</span>
                        </div>
                    </div>
                    <div class="ml-4 text-center">
                        <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">
                            {{ $book->loans_count }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">loans</div>
                    </div>
                </div>
            @empty
                <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
                    <p class="text-center text-gray-500 dark:text-gray-400">No loans this month</p>
                </div>
            @endforelse
        </div>
    </x-filament::section>

    <!-- Students with Fines -->
    @if($this->getStudentsWithFines()->count() > 0)
        <x-filament::section class="mt-6">
            <x-slot name="heading">
                <div class="flex items-center">
                    <x-heroicon-o-currency-dollar class="w-5 h-5 mr-2 text-yellow-500" />
                    Students with Outstanding Fines
                </div>
            </x-slot>

            <div class="space-y-3">
                @foreach($this->getStudentsWithFines() as $student)
                    <div class="flex items-center p-4 bg-white rounded-lg shadow-sm dark:bg-gray-800">
                        <div class="flex-1">
                            <h4 class="font-medium text-gray-900 dark:text-white">{{ $student->name }}</h4>
                            <div class="mt-1 flex items-center gap-3 text-sm text-gray-600 dark:text-gray-400">
                                <span>{{ $student->grade?->name }}</span>
                                <span>•</span>
                                <span>{{ $student->classSection?->name }}</span>
                                <span>•</span>
                                <span>ID: {{ $student->student_id }}</span>
                            </div>
                        </div>
                        <div class="ml-4 text-right">
                            <div class="text-xs text-gray-500 dark:text-gray-400">Outstanding Fine</div>
                            <div class="text-xl font-bold text-red-600 dark:text-red-400">
                                ZMW {{ number_format($student->total_fines, 2) }}
                            </div>
                        </div>
                    </div>
                @endforeach

                <div class="text-right">
                    <a href="{{ route('filament.admin.pages.student-clearance') }}"
                       class="text-sm text-primary-600 hover:underline">
                        View Student Clearance →
                    </a>
                </div>
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
