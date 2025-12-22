@extends('pdf.financial.layout')

@section('title', 'Outstanding Payables Report')

@section('period')
    As of {{ $generatedAt->format('d M Y') }}
@endsection

@section('content')
    {{-- Executive Summary --}}
    <div class="summary-section">
        <table class="summary-table">
            <tr>
                <td>
                    <div class="summary-label">Total Outstanding</div>
                    <div class="summary-value">ZMW {{ number_format($payables['total_outstanding'] ?? 0, 2) }}</div>
                </td>
                <td>
                    <div class="summary-label">Unpaid Items</div>
                    <div class="summary-value">{{ count($payables['items'] ?? []) }}</div>
                </td>
                <td>
                    <div class="summary-label">Vendors Owed</div>
                    <div class="summary-value">{{ collect($payables['items'] ?? [])->pluck('vendor')->unique()->count() }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Outstanding Expenses List --}}
    <div class="section-title">Outstanding Payables Detail</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 12%;">Date</th>
                <th style="width: 13%;">Expense #</th>
                <th style="width: 25%;">Vendor</th>
                <th style="width: 15%;" class="text-right">Total (ZMW)</th>
                <th style="width: 15%;" class="text-right">Paid (ZMW)</th>
                <th style="width: 20%;" class="text-right">Balance (ZMW)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payables['items'] ?? [] as $item)
                <tr>
                    <td>{{ isset($item['date']) ? \Carbon\Carbon::parse($item['date'])->format('d M Y') : '-' }}</td>
                    <td>{{ $item['expense_number'] ?? '-' }}</td>
                    <td>{{ $item['vendor'] ?? 'N/A' }}</td>
                    <td class="text-right">{{ number_format($item['total'] ?? 0, 2) }}</td>
                    <td class="text-right">{{ number_format($item['paid'] ?? 0, 2) }}</td>
                    <td class="text-right"><strong>{{ number_format($item['balance'] ?? 0, 2) }}</strong></td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">
                        <strong>No outstanding payables - All expenses have been paid!</strong>
                    </td>
                </tr>
            @endforelse
            @if(count($payables['items'] ?? []) > 0)
            <tr class="total-row">
                <td colspan="3"><strong>TOTAL OUTSTANDING</strong></td>
                <td class="text-right"><strong>{{ number_format(collect($payables['items'] ?? [])->sum('total'), 2) }}</strong></td>
                <td class="text-right"><strong>{{ number_format(collect($payables['items'] ?? [])->sum('paid'), 2) }}</strong></td>
                <td class="text-right"><strong>{{ number_format($payables['total_outstanding'] ?? 0, 2) }}</strong></td>
            </tr>
            @endif
        </tbody>
    </table>

    {{-- Summary by Vendor --}}
    @if(count($payables['items'] ?? []) > 0)
        @php
            $byVendor = collect($payables['items'] ?? [])->groupBy('vendor')->map(function($items, $vendor) {
                return [
                    'vendor' => $vendor,
                    'count' => $items->count(),
                    'total' => $items->sum('balance'),
                ];
            })->sortByDesc('total')->values();
        @endphp
        <div class="section-title">Summary by Vendor</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 50%;">Vendor</th>
                    <th style="width: 20%;" class="text-center">Items</th>
                    <th style="width: 25%;" class="text-right">Amount Owed (ZMW)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($byVendor as $index => $vendor)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $vendor['vendor'] }}</td>
                        <td class="text-center">{{ $vendor['count'] }}</td>
                        <td class="text-right"><strong>{{ number_format($vendor['total'], 2) }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Notes --}}
    <div class="notes-section">
        <div class="notes-title">Payment Priority Guidelines</div>
        <ul class="notes-list">
            <li>Outstanding payables should be reviewed weekly for timely payment</li>
            <li>Prioritize payments to maintain good vendor relationships</li>
            <li>Items overdue by more than 30 days should be addressed immediately</li>
            <li>Verify all amounts before processing payments</li>
        </ul>
    </div>

    {{-- Signature Section --}}
    <div class="signature-section">
        <table class="signature-table">
            <tr>
                <td>
                    <div class="signature-line">Prepared By</div>
                </td>
                <td>
                    <div class="signature-line">Authorized By</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="confidential-notice">
        This document is confidential and intended for internal use only.
    </div>
@endsection
