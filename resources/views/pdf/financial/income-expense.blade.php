@extends('pdf.financial.layout')

@section('title', 'Income & Expense Summary Report')

@section('period')
    Period: {{ $startDate->format('d M Y') }} to {{ $endDate->format('d M Y') }}
@endsection

@section('content')
    {{-- Executive Summary --}}
    <div class="summary-section">
        <table class="summary-table">
            <tr>
                <td>
                    <div class="summary-label">Total Income</div>
                    <div class="summary-value">ZMW {{ number_format($comparison['total_income'], 2) }}</div>
                </td>
                <td>
                    <div class="summary-label">Total Expenses</div>
                    <div class="summary-value">ZMW {{ number_format($comparison['total_expenses'], 2) }}</div>
                </td>
                <td>
                    <div class="summary-label">Net Income</div>
                    <div class="summary-value">ZMW {{ number_format($comparison['net_income'], 2) }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Income Breakdown --}}
    <div class="section-title">Income Analysis</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 55%;">Income Source</th>
                <th style="width: 20%;" class="text-right">Amount (ZMW)</th>
                <th style="width: 20%;" class="text-right">% of Total</th>
            </tr>
        </thead>
        <tbody>
            @php $totalIncome = $comparison['total_income'] ?: 1; @endphp
            @forelse($incomeSummary['by_account'] ?? [] as $index => $account)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $account['name'] ?? 'Unknown' }}</td>
                    <td class="text-right">{{ number_format($account['amount'] ?? 0, 2) }}</td>
                    <td class="text-right">{{ number_format((($account['amount'] ?? 0) / $totalIncome) * 100, 1) }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">No income recorded for this period</td>
                </tr>
            @endforelse
            @if(count($incomeSummary['by_account'] ?? []) > 0)
            <tr class="total-row">
                <td colspan="2"><strong>TOTAL INCOME</strong></td>
                <td class="text-right"><strong>{{ number_format($comparison['total_income'], 2) }}</strong></td>
                <td class="text-right"><strong>100%</strong></td>
            </tr>
            @endif
        </tbody>
    </table>

    {{-- Expense Breakdown --}}
    <div class="section-title">Expense Analysis</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 55%;">Expense Category</th>
                <th style="width: 20%;" class="text-right">Amount (ZMW)</th>
                <th style="width: 20%;" class="text-right">% of Total</th>
            </tr>
        </thead>
        <tbody>
            @php $totalExpenses = $comparison['total_expenses'] ?: 1; @endphp
            @forelse($expenseSummary['by_category'] ?? [] as $index => $category)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $category['name'] ?? 'Unknown' }}</td>
                    <td class="text-right">{{ number_format($category['amount'] ?? 0, 2) }}</td>
                    <td class="text-right">{{ number_format((($category['amount'] ?? 0) / $totalExpenses) * 100, 1) }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">No expenses recorded for this period</td>
                </tr>
            @endforelse
            @if(count($expenseSummary['by_category'] ?? []) > 0)
            <tr class="total-row">
                <td colspan="2"><strong>TOTAL EXPENSES</strong></td>
                <td class="text-right"><strong>{{ number_format($comparison['total_expenses'], 2) }}</strong></td>
                <td class="text-right"><strong>100%</strong></td>
            </tr>
            @endif
        </tbody>
    </table>

    {{-- Financial Summary --}}
    <div class="section-title">Financial Summary</div>
    <table class="data-table">
        <tbody>
            <tr>
                <td style="width: 70%;">Total Income</td>
                <td class="text-right">ZMW {{ number_format($comparison['total_income'], 2) }}</td>
            </tr>
            <tr>
                <td>Less: Total Expenses</td>
                <td class="text-right">(ZMW {{ number_format($comparison['total_expenses'], 2) }})</td>
            </tr>
            <tr class="total-row">
                <td><strong>NET INCOME / (LOSS)</strong></td>
                <td class="text-right"><strong>ZMW {{ number_format($comparison['net_income'], 2) }}</strong></td>
            </tr>
        </tbody>
    </table>

    {{-- Signature Section --}}
    <div class="signature-section">
        <table class="signature-table">
            <tr>
                <td>
                    <div class="signature-line">Prepared By</div>
                </td>
                <td>
                    <div class="signature-line">Approved By</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="confidential-notice">
        This document is confidential and intended for internal use only.
    </div>
@endsection
