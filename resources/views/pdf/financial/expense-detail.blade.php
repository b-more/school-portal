@extends('pdf.financial.layout')

@section('title', 'Expense Detail Report')

@section('period')
    Period: {{ $startDate->format('d M Y') }} to {{ $endDate->format('d M Y') }}
@endsection

@section('content')
    {{-- Executive Summary --}}
    @php
        $days = max(1, $startDate->diffInDays($endDate) + 1);
        $dailyAvg = ($expenseSummary['total'] ?? 0) / $days;
    @endphp
    <div class="summary-section">
        <table class="summary-table">
            <tr>
                <td>
                    <div class="summary-label">Total Expenses</div>
                    <div class="summary-value">ZMW {{ number_format($expenseSummary['total'] ?? 0, 2) }}</div>
                </td>
                <td>
                    <div class="summary-label">Categories</div>
                    <div class="summary-value">{{ count($expenseSummary['by_category'] ?? []) }}</div>
                </td>
                <td>
                    <div class="summary-label">Daily Average</div>
                    <div class="summary-value">ZMW {{ number_format($dailyAvg, 2) }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Expense by Category --}}
    <div class="section-title">Expenses by Category</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 50%;">Category</th>
                <th style="width: 20%;" class="text-right">Amount (ZMW)</th>
                <th style="width: 15%;" class="text-right">% of Total</th>
                <th style="width: 10%;" class="text-center">Bar</th>
            </tr>
        </thead>
        <tbody>
            @php
                $total = $expenseSummary['total'] ?: 1;
                $categories = collect($expenseSummary['by_category'] ?? [])->sortByDesc('amount');
            @endphp
            @forelse($categories as $index => $category)
                @php $percentage = (($category['amount'] ?? 0) / $total) * 100; @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $category['name'] ?? 'Unknown' }}</td>
                    <td class="text-right">{{ number_format($category['amount'] ?? 0, 2) }}</td>
                    <td class="text-right">{{ number_format($percentage, 1) }}%</td>
                    <td>
                        <div class="progress-bar-container">
                            <div class="progress-bar" style="width: {{ min(100, $percentage) }}%;"></div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">No expenses recorded for this period</td>
                </tr>
            @endforelse
            @if(count($expenseSummary['by_category'] ?? []) > 0)
            <tr class="total-row">
                <td colspan="2"><strong>TOTAL EXPENSES</strong></td>
                <td class="text-right"><strong>{{ number_format($expenseSummary['total'] ?? 0, 2) }}</strong></td>
                <td class="text-right"><strong>100%</strong></td>
                <td></td>
            </tr>
            @endif
        </tbody>
    </table>

    {{-- Notes --}}
    <div class="notes-section">
        <div class="notes-title">Notes</div>
        <ul class="notes-list">
            <li>This report shows all expenses recorded within the specified period</li>
            <li>Expenses are grouped by category for easy analysis</li>
            <li>For detailed transaction listings, please refer to individual expense records</li>
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
                    <div class="signature-line">Reviewed By</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="confidential-notice">
        This document is confidential and intended for internal use only.
    </div>
@endsection
