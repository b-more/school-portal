@extends('pdf.financial.layout')

@section('title', 'Income Detail Report')

@section('period')
    Period: {{ $startDate->format('d M Y') }} to {{ $endDate->format('d M Y') }}
@endsection

@section('content')
    {{-- Executive Summary --}}
    @php
        $days = max(1, $startDate->diffInDays($endDate) + 1);
        $dailyAvg = ($incomeSummary['total'] ?? 0) / $days;
    @endphp
    <div class="summary-section">
        <table class="summary-table">
            <tr>
                <td>
                    <div class="summary-label">Total Income</div>
                    <div class="summary-value">ZMW {{ number_format($incomeSummary['total'] ?? 0, 2) }}</div>
                </td>
                <td>
                    <div class="summary-label">Income Sources</div>
                    <div class="summary-value">{{ count($incomeSummary['by_account'] ?? []) }}</div>
                </td>
                <td>
                    <div class="summary-label">Daily Average</div>
                    <div class="summary-value">ZMW {{ number_format($dailyAvg, 2) }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Income by Source --}}
    <div class="section-title">Income by Source</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 50%;">Income Source</th>
                <th style="width: 20%;" class="text-right">Amount (ZMW)</th>
                <th style="width: 15%;" class="text-right">% of Total</th>
                <th style="width: 10%;" class="text-center">Bar</th>
            </tr>
        </thead>
        <tbody>
            @php
                $total = $incomeSummary['total'] ?: 1;
                $sources = collect($incomeSummary['by_account'] ?? [])->sortByDesc('amount');
            @endphp
            @forelse($sources as $index => $source)
                @php $percentage = (($source['amount'] ?? 0) / $total) * 100; @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $source['name'] ?? 'Unknown' }}</td>
                    <td class="text-right">{{ number_format($source['amount'] ?? 0, 2) }}</td>
                    <td class="text-right">{{ number_format($percentage, 1) }}%</td>
                    <td>
                        <div class="progress-bar-container">
                            <div class="progress-bar" style="width: {{ min(100, $percentage) }}%;"></div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">No income recorded for this period</td>
                </tr>
            @endforelse
            @if(count($incomeSummary['by_account'] ?? []) > 0)
            <tr class="total-row">
                <td colspan="2"><strong>TOTAL INCOME</strong></td>
                <td class="text-right"><strong>{{ number_format($incomeSummary['total'] ?? 0, 2) }}</strong></td>
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
            <li>This report shows all income recorded within the specified period</li>
            <li>Income is grouped by source/account for easy analysis</li>
            <li>School fees typically represent the primary source of income</li>
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
