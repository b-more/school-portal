<x-filament-panels::page>
    {{-- Top Header with User Guide --}}
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Financial Overview</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Monitor income, expenses, and cash flow</p>
        </div>
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" type="button" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
                <x-heroicon-o-book-open class="w-5 h-5 mr-2" />
                Accountant User Guide
                <x-heroicon-o-chevron-down class="w-4 h-4 ml-2" />
            </button>
            <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute right-0 mt-2 w-56 rounded-lg bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5 z-50">
                <div class="py-1">
                    <a href="{{ route('guides.accountant') }}" target="_blank" class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                        <x-heroicon-o-eye class="w-4 h-4 mr-3 text-gray-400" />
                        View PDF in Browser
                    </a>
                    <a href="{{ route('guides.accountant.download') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                        <x-heroicon-o-document-arrow-down class="w-4 h-4 mr-3 text-red-500" />
                        Download PDF
                    </a>
                    <a href="{{ route('guides.accountant.docx') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                        <x-heroicon-o-document-text class="w-4 h-4 mr-3 text-blue-500" />
                        Download Word (DOCX)
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Period Selector --}}
    <div class="mb-6">
        <div class="flex flex-wrap gap-2">
            @foreach(['week' => 'This Week', 'month' => 'This Month', 'quarter' => 'This Quarter', 'year' => 'This Year'] as $key => $label)
                <button
                    wire:click="setPeriod('{{ $key }}')"
                    class="px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ $period === $key ? 'bg-primary-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700' }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
            {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
        </p>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        {{-- Total Income --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Income</p>
                    <p class="text-2xl font-bold text-success-600 dark:text-success-400">
                        ZMW {{ number_format($totalIncome, 2) }}
                    </p>
                </div>
                <div class="p-3 bg-success-100 dark:bg-success-900/30 rounded-full">
                    <x-heroicon-o-arrow-trending-up class="w-6 h-6 text-success-600 dark:text-success-400" />
                </div>
            </div>
        </div>

        {{-- Total Expenses --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Expenses</p>
                    <p class="text-2xl font-bold text-danger-600 dark:text-danger-400">
                        ZMW {{ number_format($totalExpenses, 2) }}
                    </p>
                </div>
                <div class="p-3 bg-danger-100 dark:bg-danger-900/30 rounded-full">
                    <x-heroicon-o-arrow-trending-down class="w-6 h-6 text-danger-600 dark:text-danger-400" />
                </div>
            </div>
        </div>

        {{-- Net Income --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Net Income</p>
                    <p class="text-2xl font-bold {{ $netIncome >= 0 ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}">
                        ZMW {{ number_format($netIncome, 2) }}
                    </p>
                </div>
                <div class="p-3 {{ $netIncome >= 0 ? 'bg-success-100 dark:bg-success-900/30' : 'bg-danger-100 dark:bg-danger-900/30' }} rounded-full">
                    <x-heroicon-o-banknotes class="w-6 h-6 {{ $netIncome >= 0 ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}" />
                </div>
            </div>
        </div>

        {{-- Outstanding Payables --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Outstanding Payables</p>
                    <p class="text-2xl font-bold text-warning-600 dark:text-warning-400">
                        ZMW {{ number_format($outstandingPayables, 2) }}
                    </p>
                </div>
                <div class="p-3 bg-warning-100 dark:bg-warning-900/30 rounded-full">
                    <x-heroicon-o-clock class="w-6 h-6 text-warning-600 dark:text-warning-400" />
                </div>
            </div>
        </div>
    </div>

    {{-- Bank Balances --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Bank Account Balances</h3>
        @if(count($bankBalances) > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($bankBalances as $bank)
                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $bank['bank_name'] }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $bank['account_number'] }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-bold {{ $bank['current_balance'] >= 0 ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}">
                                {{ $bank['currency'] }} {{ number_format($bank['current_balance'], 2) }}
                            </p>
                            @if($bank['is_default'])
                                <span class="text-xs text-primary-600 dark:text-primary-400">Default</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500 dark:text-gray-400">No bank accounts configured.</p>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Income by Category --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Income by Category</h3>
            @if(count($incomeByCategory) > 0)
                <div class="space-y-3">
                    @foreach($incomeByCategory as $item)
                        <div class="flex items-center justify-between">
                            <span class="text-gray-700 dark:text-gray-300">{{ $item['account']->name ?? 'Unknown' }}</span>
                            <span class="font-medium text-success-600 dark:text-success-400">
                                ZMW {{ number_format($item['total'], 2) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 dark:text-gray-400">No income recorded for this period.</p>
            @endif
        </div>

        {{-- Expenses by Category --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Expenses by Category</h3>
            @if(count($expensesByCategory) > 0)
                <div class="space-y-3">
                    @foreach($expensesByCategory as $item)
                        <div class="flex items-center justify-between">
                            <span class="text-gray-700 dark:text-gray-300">{{ $item['category']->name ?? 'Unknown' }}</span>
                            <span class="font-medium text-danger-600 dark:text-danger-400">
                                ZMW {{ number_format($item['total'], 2) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 dark:text-gray-400">No expenses recorded for this period.</p>
            @endif
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="mt-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Actions</h3>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('filament.admin.resources.accounting.expenses.create') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                <x-heroicon-o-plus class="w-5 h-5 mr-2" />
                Record Expense
            </a>
            <a href="{{ route('filament.admin.resources.accounting.payment-vouchers.create') }}" class="inline-flex items-center px-4 py-2 bg-success-600 text-white rounded-lg hover:bg-success-700 transition-colors">
                <x-heroicon-o-document-check class="w-5 h-5 mr-2" />
                Create Payment Voucher
            </a>
            <a href="{{ route('filament.admin.resources.accounting.bank-transactions.create') }}" class="inline-flex items-center px-4 py-2 bg-info-600 text-white rounded-lg hover:bg-info-700 transition-colors">
                <x-heroicon-o-arrows-right-left class="w-5 h-5 mr-2" />
                Record Bank Transaction
            </a>
            <a href="{{ route('filament.admin.pages.financial-reports') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                <x-heroicon-o-document-chart-bar class="w-5 h-5 mr-2" />
                View Reports
            </a>
        </div>
    </div>
</x-filament-panels::page>
