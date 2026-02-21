<div class="p-6 text-center">
    <div class="mb-4">
        <h3 class="text-lg font-bold">Payment QR Code</h3>
        <p class="text-sm text-gray-600">Scan this QR code with your mobile money app to pay</p>
    </div>

    <div class="flex justify-center mb-6">
        {{-- QR Code will be generated here --}}
        <div class="border-4 border-gray-300 p-4 rounded-lg bg-white">
            {!! QrCode::size(250)->generate($record->qr_code) !!}
        </div>
    </div>

    <div class="space-y-2 text-left bg-gray-50 p-4 rounded-lg">
        <div class="grid grid-cols-2 gap-2">
            <div class="font-semibold">Reference:</div>
            <div class="font-mono">{{ $record->payment_reference }}</div>

            <div class="font-semibold">Amount:</div>
            <div class="text-green-600 font-bold">ZMW {{ number_format($record->amount, 2) }}</div>

            <div class="font-semibold">Student:</div>
            <div>{{ $record->student?->name }}</div>

            <div class="font-semibold">Mobile:</div>
            <div>{{ $record->customer_mobile }}</div>

            <div class="font-semibold">Status:</div>
            <div>
                <span class="px-2 py-1 rounded text-xs font-semibold
                    {{ $record->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                    {{ $record->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                    {{ $record->status === 'failed' ? 'bg-red-100 text-red-800' : '' }}
                ">
                    {{ ucfirst($record->status) }}
                </span>
            </div>

            <div class="font-semibold">Expires:</div>
            <div>{{ $record->expires_at?->format('Y-m-d H:i') }}</div>
        </div>
    </div>

    <div class="mt-4 p-3 bg-blue-50 rounded-lg text-sm">
        <p class="font-semibold mb-2">Instructions:</p>
        <ol class="list-decimal list-inside text-left space-y-1">
            <li>Open your mobile money app (MTN, Airtel, or Zamtel)</li>
            <li>Scan this QR code or enter reference: <strong>{{ $record->payment_reference }}</strong></li>
            <li>Confirm the amount: <strong>ZMW {{ number_format($record->amount, 2) }}</strong></li>
            <li>Complete the payment</li>
        </ol>
    </div>
</div>
