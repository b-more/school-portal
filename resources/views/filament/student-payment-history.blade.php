<div class="space-y-6">
    {{-- Student Information Header --}}
    <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
        <div class="flex items-center space-x-4">
            <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center">
                    <span class="text-white font-semibold text-lg">
                        {{ substr($student->name, 0, 1) }}
                    </span>
                </div>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ $student->name }}
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Student ID: {{ $student->student_id_number ?? 'Not assigned' }}
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Grade: {{ $student->grade->name ?? 'Not assigned' }}
                </p>
            </div>
        </div>
    </div>

    {{-- Payment Summary --}}
    @php
        $totalFees = collect($history)->sum('total_fee');
        $totalPaid = collect($history)->sum('amount_paid');
        $totalOutstanding = collect($history)->sum('balance');
    @endphp

    <div class="grid grid-cols-3 gap-4">
        <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg text-center">
            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                ZMW {{ number_format($totalFees, 2) }}
            </div>
            <div class="text-sm text-blue-600 dark:text-blue-400">Total Fees</div>
        </div>

        <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg text-center">
            <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                ZMW {{ number_format($totalPaid, 2) }}
            </div>
            <div class="text-sm text-green-600 dark:text-green-400">Total Paid</div>
        </div>

        <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg text-center">
            <div class="text-2xl font-bold text-red-600 dark:text-red-400">
                ZMW {{ number_format($totalOutstanding, 2) }}
            </div>
            <div class="text-sm text-red-600 dark:text-red-400">Outstanding</div>
        </div>
    </div>

    {{-- Payment History Timeline --}}
    <div class="space-y-4">
        <h4 class="text-lg font-semibold text-gray-900 dark:text-white">Payment History</h4>

        @if(empty($history))
            <div class="text-center py-8">
                <div class="text-gray-400 dark:text-gray-600">
                    <svg class="mx-auto h-12 w-12 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="text-lg">No payment history found</p>
                    <p class="text-sm">Payment records will appear here once fees are assigned to this student.</p>
                </div>
            </div>
        @else
            <div class="space-y-4 max-h-96 overflow-y-auto">
                @foreach($history as $record)
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                        {{-- Term Header --}}
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h5 class="font-semibold text-gray-900 dark:text-white">
                                    {{ $record['term'] }} ({{ $record['academic_year'] }})
                                </h5>
                                @if($record['payment_date'])
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        Payment Date: {{ \Carbon\Carbon::parse($record['payment_date'])->format('M j, Y') }}
                                    </p>
                                @endif
                            </div>

                            {{-- Payment Status Badge --}}
                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                @if($record['payment_status'] === 'paid')
                                    bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400
                                @elseif($record['payment_status'] === 'partial')
                                    bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400
                                @elseif($record['payment_status'] === 'overpaid')
                                    bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400
                                @else
                                    bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400
                                @endif
                            ">
                                {{ ucfirst($record['payment_status']) }}
                            </span>
                        </div>

                        {{-- Payment Details Grid --}}
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            <div>
                                <span class="text-gray-600 dark:text-gray-400">Total Fee</span>
                                <div class="font-semibold text-gray-900 dark:text-white">
                                    ZMW {{ number_format($record['total_fee'], 2) }}
                                </div>
                            </div>

                            <div>
                                <span class="text-gray-600 dark:text-gray-400">Amount Paid</span>
                                <div class="font-semibold text-green-600 dark:text-green-400">
                                    ZMW {{ number_format($record['amount_paid'], 2) }}
                                </div>
                            </div>

                            <div>
                                <span class="text-gray-600 dark:text-gray-400">Balance</span>
                                <div class="font-semibold {{ $record['balance'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                    ZMW {{ number_format($record['balance'], 2) }}
                                </div>
                            </div>

                            <div>
                                <span class="text-gray-600 dark:text-gray-400">Progress</span>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 mt-1">
                                    @php
                                        $progress = $record['total_fee'] > 0 ? ($record['amount_paid'] / $record['total_fee']) * 100 : 0;
                                        $progress = min(100, max(0, $progress));
                                    @endphp
                                    <div class="h-2 rounded-full {{ $progress >= 100 ? 'bg-green-500' : ($progress > 0 ? 'bg-yellow-500' : 'bg-red-500') }}"
                                         style="width: {{ $progress }}%"></div>
                                </div>
                                <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                    {{ number_format($progress, 1) }}%
                                </div>
                            </div>
                        </div>

                        {{-- Transaction Details (if available) --}}
                        @if(!empty($record['transactions']))
                            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <h6 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Transaction Details</h6>
                                <div class="space-y-2">
                                    @foreach($record['transactions'] as $transaction)
                                        <div class="flex justify-between items-center text-sm">
                                            <div class="flex items-center space-x-2">
                                                <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                                                <span class="text-gray-700 dark:text-gray-300">
                                                    {{ $transaction['formatted_type'] ?? ucfirst($transaction['type']) }}
                                                </span>
                                                @if(!empty($transaction['reference']))
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                                        ({{ $transaction['reference'] }})
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-right">
                                                <div class="font-semibold text-gray-900 dark:text-white">
                                                    ZMW {{ number_format($transaction['amount'], 2) }}
                                                </div>
                                                @if(!empty($transaction['payment_method']))
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ $transaction['payment_method'] }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Collection Rate --}}
    @if(!empty($history))
        <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600 dark:text-gray-400">Overall Collection Rate</span>
                @php
                    $collectionRate = $totalFees > 0 ? ($totalPaid / $totalFees) * 100 : 0;
                @endphp
                <div class="flex items-center space-x-2">
                    <div class="w-24 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="h-2 rounded-full {{ $collectionRate >= 100 ? 'bg-green-500' : ($collectionRate >= 50 ? 'bg-yellow-500' : 'bg-red-500') }}"
                             style="width: {{ min(100, $collectionRate) }}%"></div>
                    </div>
                    <span class="text-sm font-semibold text-gray-900 dark:text-white">
                        {{ number_format($collectionRate, 1) }}%
                    </span>
                </div>
            </div>
        </div>
    @endif
</div>
