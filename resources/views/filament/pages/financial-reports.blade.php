<x-filament-panels::page>
    <form wire:submit.prevent>
        {{ $this->form }}
    </form>

    <div class="mt-6">
        {{-- Report Header --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $this->getReportTitle() }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Period: {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
                    </p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ $this->getExportUrl() }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                        <x-heroicon-o-document-arrow-down class="w-5 h-5 mr-2" />
                        Export PDF
                    </a>
                    <button onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                        <x-heroicon-o-printer class="w-5 h-5 mr-2" />
                        Print
                    </button>
                </div>
            </div>
        </div>

        {{-- Income vs Expense Report --}}
        @if($reportType === 'income_expense' && !empty($reportData))
            {{-- Summary Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-success-50 dark:bg-success-900/20 rounded-xl p-6 border border-success-200 dark:border-success-800">
                    <p class="text-sm font-medium text-success-700 dark:text-success-300">Total Income</p>
                    <p class="text-3xl font-bold text-success-600 dark:text-success-400">
                        ZMW {{ number_format($reportData['comparison']['total_income'] ?? 0, 2) }}
                    </p>
                </div>
                <div class="bg-danger-50 dark:bg-danger-900/20 rounded-xl p-6 border border-danger-200 dark:border-danger-800">
                    <p class="text-sm font-medium text-danger-700 dark:text-danger-300">Total Expenses</p>
                    <p class="text-3xl font-bold text-danger-600 dark:text-danger-400">
                        ZMW {{ number_format($reportData['comparison']['total_expenses'] ?? 0, 2) }}
                    </p>
                </div>
                <div class="bg-{{ ($reportData['comparison']['net_income'] ?? 0) >= 0 ? 'primary' : 'warning' }}-50 dark:bg-{{ ($reportData['comparison']['net_income'] ?? 0) >= 0 ? 'primary' : 'warning' }}-900/20 rounded-xl p-6 border border-{{ ($reportData['comparison']['net_income'] ?? 0) >= 0 ? 'primary' : 'warning' }}-200 dark:border-{{ ($reportData['comparison']['net_income'] ?? 0) >= 0 ? 'primary' : 'warning' }}-800">
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Net Income</p>
                    <p class="text-3xl font-bold {{ ($reportData['comparison']['net_income'] ?? 0) >= 0 ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}">
                        ZMW {{ number_format($reportData['comparison']['net_income'] ?? 0, 2) }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Income Breakdown --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Income Breakdown</h3>
                    @if(!empty($reportData['income']['by_account']))
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <th class="text-left py-2 text-sm font-medium text-gray-500 dark:text-gray-400">Category</th>
                                    <th class="text-right py-2 text-sm font-medium text-gray-500 dark:text-gray-400">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reportData['income']['by_account'] as $item)
                                    <tr class="border-b border-gray-100 dark:border-gray-700">
                                        <td class="py-2 text-gray-700 dark:text-gray-300">{{ $item['account']->name ?? 'Unknown' }}</td>
                                        <td class="py-2 text-right font-medium text-success-600 dark:text-success-400">
                                            ZMW {{ number_format($item['total'], 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="font-bold">
                                    <td class="py-2 text-gray-900 dark:text-white">Total</td>
                                    <td class="py-2 text-right text-success-600 dark:text-success-400">
                                        ZMW {{ number_format($reportData['income']['total_income'] ?? 0, 2) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    @else
                        <p class="text-gray-500 dark:text-gray-400">No income data for this period.</p>
                    @endif
                </div>

                {{-- Expense Breakdown --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Expense Breakdown</h3>
                    @if(!empty($reportData['expenses']['by_category']))
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <th class="text-left py-2 text-sm font-medium text-gray-500 dark:text-gray-400">Category</th>
                                    <th class="text-right py-2 text-sm font-medium text-gray-500 dark:text-gray-400">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reportData['expenses']['by_category'] as $item)
                                    <tr class="border-b border-gray-100 dark:border-gray-700">
                                        <td class="py-2 text-gray-700 dark:text-gray-300">{{ $item['category']->name ?? 'Unknown' }}</td>
                                        <td class="py-2 text-right font-medium text-danger-600 dark:text-danger-400">
                                            ZMW {{ number_format($item['total'], 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="font-bold">
                                    <td class="py-2 text-gray-900 dark:text-white">Total</td>
                                    <td class="py-2 text-right text-danger-600 dark:text-danger-400">
                                        ZMW {{ number_format($reportData['expenses']['total_expenses'] ?? 0, 2) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    @else
                        <p class="text-gray-500 dark:text-gray-400">No expense data for this period.</p>
                    @endif
                </div>
            </div>
        @endif

        {{-- Cash Flow Report --}}
        @if($reportType === 'cash_flow' && !empty($reportData))
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Inflows --}}
                        <div class="p-4 bg-success-50 dark:bg-success-900/20 rounded-lg">
                            <h4 class="font-semibold text-success-700 dark:text-success-300 mb-3">Cash Inflows</h4>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Fee Income</span>
                                    <span class="font-medium">ZMW {{ number_format($reportData['inflows']['fee_income'] ?? 0, 2) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Other Income</span>
                                    <span class="font-medium">ZMW {{ number_format($reportData['inflows']['other_income'] ?? 0, 2) }}</span>
                                </div>
                                <div class="flex justify-between border-t border-success-200 dark:border-success-700 pt-2 font-bold">
                                    <span>Total Inflows</span>
                                    <span class="text-success-600 dark:text-success-400">ZMW {{ number_format($reportData['inflows']['total'] ?? 0, 2) }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Outflows --}}
                        <div class="p-4 bg-danger-50 dark:bg-danger-900/20 rounded-lg">
                            <h4 class="font-semibold text-danger-700 dark:text-danger-300 mb-3">Cash Outflows</h4>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Salary Expenses</span>
                                    <span class="font-medium">ZMW {{ number_format($reportData['outflows']['salary_expenses'] ?? 0, 2) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Operating Expenses</span>
                                    <span class="font-medium">ZMW {{ number_format($reportData['outflows']['operating_expenses'] ?? 0, 2) }}</span>
                                </div>
                                <div class="flex justify-between border-t border-danger-200 dark:border-danger-700 pt-2 font-bold">
                                    <span>Total Outflows</span>
                                    <span class="text-danger-600 dark:text-danger-400">ZMW {{ number_format($reportData['outflows']['total'] ?? 0, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Summary --}}
                    <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Opening Balance</p>
                                <p class="text-xl font-bold text-gray-900 dark:text-white">ZMW {{ number_format($reportData['opening_balance'] ?? 0, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Net Cash Flow</p>
                                <p class="text-xl font-bold {{ ($reportData['net_cash_flow'] ?? 0) >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                                    ZMW {{ number_format($reportData['net_cash_flow'] ?? 0, 2) }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Closing Balance</p>
                                <p class="text-xl font-bold text-gray-900 dark:text-white">ZMW {{ number_format($reportData['closing_balance'] ?? 0, 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Outstanding Payables Report --}}
        @if($reportType === 'payables' && !empty($reportData))
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Outstanding Payables</h3>
                    <span class="text-xl font-bold text-danger-600 dark:text-danger-400">
                        Total: ZMW {{ number_format($reportData['total_outstanding'] ?? 0, 2) }}
                    </span>
                </div>

                @if(!empty($reportData['items']))
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <th class="text-left py-3 text-sm font-medium text-gray-500 dark:text-gray-400">Expense #</th>
                                    <th class="text-left py-3 text-sm font-medium text-gray-500 dark:text-gray-400">Date</th>
                                    <th class="text-left py-3 text-sm font-medium text-gray-500 dark:text-gray-400">Vendor</th>
                                    <th class="text-left py-3 text-sm font-medium text-gray-500 dark:text-gray-400">Description</th>
                                    <th class="text-right py-3 text-sm font-medium text-gray-500 dark:text-gray-400">Total</th>
                                    <th class="text-right py-3 text-sm font-medium text-gray-500 dark:text-gray-400">Paid</th>
                                    <th class="text-right py-3 text-sm font-medium text-gray-500 dark:text-gray-400">Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reportData['items'] as $item)
                                    <tr class="border-b border-gray-100 dark:border-gray-700">
                                        <td class="py-3 font-medium text-gray-900 dark:text-white">{{ $item['expense_number'] }}</td>
                                        <td class="py-3 text-gray-600 dark:text-gray-400">{{ \Carbon\Carbon::parse($item['date'])->format('M d, Y') }}</td>
                                        <td class="py-3 text-gray-600 dark:text-gray-400">{{ $item['vendor'] }}</td>
                                        <td class="py-3 text-gray-600 dark:text-gray-400">{{ Str::limit($item['description'], 30) }}</td>
                                        <td class="py-3 text-right text-gray-700 dark:text-gray-300">ZMW {{ number_format($item['total'], 2) }}</td>
                                        <td class="py-3 text-right text-success-600 dark:text-success-400">ZMW {{ number_format($item['paid'], 2) }}</td>
                                        <td class="py-3 text-right font-medium text-danger-600 dark:text-danger-400">ZMW {{ number_format($item['balance'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-500 dark:text-gray-400 text-center py-8">No outstanding payables.</p>
                @endif
            </div>
        @endif

        {{-- Income Detail Report --}}
        @if($reportType === 'income_detail' && !empty($reportData))
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="mb-6 p-4 bg-success-50 dark:bg-success-900/20 rounded-lg">
                    <h3 class="text-lg font-semibold text-success-700 dark:text-success-300">
                        Total Income: ZMW {{ number_format($reportData['total_income'] ?? 0, 2) }}
                    </h3>
                </div>

                <h4 class="font-medium text-gray-900 dark:text-white mb-3">By Account</h4>
                @if(!empty($reportData['by_account']))
                    <table class="w-full mb-6">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="text-left py-2 text-sm font-medium text-gray-500 dark:text-gray-400">Account</th>
                                <th class="text-right py-2 text-sm font-medium text-gray-500 dark:text-gray-400">Count</th>
                                <th class="text-right py-2 text-sm font-medium text-gray-500 dark:text-gray-400">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reportData['by_account'] as $item)
                                <tr class="border-b border-gray-100 dark:border-gray-700">
                                    <td class="py-2 text-gray-700 dark:text-gray-300">{{ $item['account']->name ?? 'Unknown' }}</td>
                                    <td class="py-2 text-right text-gray-600 dark:text-gray-400">{{ $item['count'] }}</td>
                                    <td class="py-2 text-right font-medium text-success-600 dark:text-success-400">
                                        ZMW {{ number_format($item['total'], 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

                <h4 class="font-medium text-gray-900 dark:text-white mb-3">By Payment Method</h4>
                @if(!empty($reportData['by_payment_method']))
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="text-left py-2 text-sm font-medium text-gray-500 dark:text-gray-400">Method</th>
                                <th class="text-right py-2 text-sm font-medium text-gray-500 dark:text-gray-400">Count</th>
                                <th class="text-right py-2 text-sm font-medium text-gray-500 dark:text-gray-400">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reportData['by_payment_method'] as $item)
                                <tr class="border-b border-gray-100 dark:border-gray-700">
                                    <td class="py-2 text-gray-700 dark:text-gray-300">{{ ucfirst(str_replace('_', ' ', $item['method'] ?? 'Unknown')) }}</td>
                                    <td class="py-2 text-right text-gray-600 dark:text-gray-400">{{ $item['count'] }}</td>
                                    <td class="py-2 text-right font-medium text-success-600 dark:text-success-400">
                                        ZMW {{ number_format($item['total'], 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        @endif

        {{-- Expense Detail Report --}}
        @if($reportType === 'expense_detail' && !empty($reportData))
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="p-4 bg-danger-50 dark:bg-danger-900/20 rounded-lg">
                        <p class="text-sm text-danger-600 dark:text-danger-400">Total Expenses</p>
                        <p class="text-xl font-bold text-danger-700 dark:text-danger-300">ZMW {{ number_format($reportData['total_expenses'] ?? 0, 2) }}</p>
                    </div>
                    <div class="p-4 bg-success-50 dark:bg-success-900/20 rounded-lg">
                        <p class="text-sm text-success-600 dark:text-success-400">Total Paid</p>
                        <p class="text-xl font-bold text-success-700 dark:text-success-300">ZMW {{ number_format($reportData['total_paid'] ?? 0, 2) }}</p>
                    </div>
                    <div class="p-4 bg-warning-50 dark:bg-warning-900/20 rounded-lg">
                        <p class="text-sm text-warning-600 dark:text-warning-400">Total Unpaid</p>
                        <p class="text-xl font-bold text-warning-700 dark:text-warning-300">ZMW {{ number_format($reportData['total_unpaid'] ?? 0, 2) }}</p>
                    </div>
                </div>

                <h4 class="font-medium text-gray-900 dark:text-white mb-3">By Category</h4>
                @if(!empty($reportData['by_category']))
                    <table class="w-full mb-6">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="text-left py-2 text-sm font-medium text-gray-500 dark:text-gray-400">Category</th>
                                <th class="text-right py-2 text-sm font-medium text-gray-500 dark:text-gray-400">Count</th>
                                <th class="text-right py-2 text-sm font-medium text-gray-500 dark:text-gray-400">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reportData['by_category'] as $item)
                                <tr class="border-b border-gray-100 dark:border-gray-700">
                                    <td class="py-2 text-gray-700 dark:text-gray-300">{{ $item['category']->name ?? 'Unknown' }}</td>
                                    <td class="py-2 text-right text-gray-600 dark:text-gray-400">{{ $item['count'] }}</td>
                                    <td class="py-2 text-right font-medium text-danger-600 dark:text-danger-400">
                                        ZMW {{ number_format($item['total'], 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

                <h4 class="font-medium text-gray-900 dark:text-white mb-3">By Vendor</h4>
                @if(!empty($reportData['by_vendor']))
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="text-left py-2 text-sm font-medium text-gray-500 dark:text-gray-400">Vendor</th>
                                <th class="text-right py-2 text-sm font-medium text-gray-500 dark:text-gray-400">Count</th>
                                <th class="text-right py-2 text-sm font-medium text-gray-500 dark:text-gray-400">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reportData['by_vendor'] as $item)
                                <tr class="border-b border-gray-100 dark:border-gray-700">
                                    <td class="py-2 text-gray-700 dark:text-gray-300">{{ $item['vendor']->name ?? 'No Vendor' }}</td>
                                    <td class="py-2 text-right text-gray-600 dark:text-gray-400">{{ $item['count'] }}</td>
                                    <td class="py-2 text-right font-medium text-danger-600 dark:text-danger-400">
                                        ZMW {{ number_format($item['total'], 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        @endif
    </div>
</x-filament-panels::page>
