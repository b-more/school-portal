<div class="p-4">
    <div class="space-y-4">
        @forelse($fee->paymentTransactions()->orderBy('transaction_date', 'desc')->get() as $transaction)
            <div class="flex items-start justify-between border-b pb-3">
                <div class="flex-1">
                    <div class="font-medium text-gray-900 dark:text-gray-100">
                        {{ $transaction->formatted_type ?? ucfirst($transaction->type) }}
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $transaction->transaction_date->format('M d, Y') }}
                    </div>
                    @if($transaction->notes)
                        <div class="text-sm text-gray-500 dark:text-gray-500 mt-1">
                            {{ $transaction->notes }}
                        </div>
                    @endif
                </div>
                <div class="text-right">
                    <div class="font-semibold text-gray-900 dark:text-gray-100">
                        ZMW {{ number_format($transaction->amount, 2) }}
                    </div>
                    @if($transaction->payment_method)
                        <div class="text-xs text-gray-500 dark:text-gray-500">
                            {{ $transaction->formatted_payment_method ?? $transaction->payment_method }}
                        </div>
                    @endif
                    @if($transaction->reference_number)
                        <div class="text-xs text-gray-500 dark:text-gray-500">
                            Ref: {{ $transaction->reference_number }}
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                No payment transactions found
            </div>
        @endforelse

        <div class="pt-4 mt-4 border-t">
            <div class="flex justify-between items-center font-semibold">
                <span>Total Paid:</span>
                <span class="text-green-600 dark:text-green-400">
                    ZMW {{ number_format($fee->amount_paid, 2) }}
                </span>
            </div>
            <div class="flex justify-between items-center font-semibold mt-2">
                <span>Remaining Balance:</span>
                <span class="text-red-600 dark:text-red-400">
                    ZMW {{ number_format($fee->balance, 2) }}
                </span>
            </div>
        </div>
    </div>
</div>
