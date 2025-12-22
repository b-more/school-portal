@extends('pdf.financial.layout')

@section('title', 'Cash Flow Report')

@section('period')
    As of {{ $endDate->format('d M Y') }}
@endsection

@section('content')
    {{-- Bank Balances Summary --}}
    @php $totalBalance = collect($bankBalances)->sum('balance'); @endphp
    <div class="summary-section">
        <table class="summary-table">
            <tr>
                <td colspan="3">
                    <div class="summary-label">Total Cash & Bank Balances</div>
                    <div class="summary-value">ZMW {{ number_format($totalBalance, 2) }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Bank Account Details --}}
    <div class="section-title">Bank Account Balances</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 35%;">Account Name</th>
                <th style="width: 25%;">Bank</th>
                <th style="width: 15%;">Account Number</th>
                <th style="width: 20%;" class="text-right">Balance (ZMW)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bankBalances as $index => $bank)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $bank['name'] ?? 'Unknown' }}</td>
                    <td>{{ $bank['bank_name'] ?? '-' }}</td>
                    <td>{{ $bank['account_number'] ?? '-' }}</td>
                    <td class="text-right">{{ number_format($bank['balance'] ?? 0, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">No bank accounts configured</td>
                </tr>
            @endforelse
            @if(count($bankBalances) > 0)
            <tr class="total-row">
                <td colspan="4"><strong>TOTAL CASH & BANK BALANCES</strong></td>
                <td class="text-right"><strong>ZMW {{ number_format($totalBalance, 2) }}</strong></td>
            </tr>
            @endif
        </tbody>
    </table>

    {{-- Monthly Cash Flow Trend --}}
    <div class="section-title">Monthly Cash Flow Trend ({{ date('Y') }})</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25%;">Month</th>
                <th style="width: 25%;" class="text-right">Income (ZMW)</th>
                <th style="width: 25%;" class="text-right">Expenses (ZMW)</th>
                <th style="width: 25%;" class="text-right">Net Flow (ZMW)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $yearlyIncome = 0;
                $yearlyExpenses = 0;
                $months = ['January', 'February', 'March', 'April', 'May', 'June',
                          'July', 'August', 'September', 'October', 'November', 'December'];
            @endphp
            @foreach($months as $index => $month)
                @php
                    $monthData = collect($monthlyTrend['months'] ?? $monthlyTrend ?? [])->firstWhere('month_number', $index + 1) ?? ['income' => 0, 'expenses' => 0];
                    $income = $monthData['income'] ?? 0;
                    $expenses = $monthData['expenses'] ?? 0;
                    $net = $income - $expenses;
                    $yearlyIncome += $income;
                    $yearlyExpenses += $expenses;
                @endphp
                <tr>
                    <td>{{ $month }}</td>
                    <td class="text-right">{{ number_format($income, 2) }}</td>
                    <td class="text-right">{{ number_format($expenses, 2) }}</td>
                    <td class="text-right">{{ number_format($net, 2) }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td><strong>YEAR TOTAL</strong></td>
                <td class="text-right"><strong>{{ number_format($yearlyIncome, 2) }}</strong></td>
                <td class="text-right"><strong>{{ number_format($yearlyExpenses, 2) }}</strong></td>
                <td class="text-right"><strong>{{ number_format($yearlyIncome - $yearlyExpenses, 2) }}</strong></td>
            </tr>
        </tbody>
    </table>

    {{-- Notes --}}
    <div class="notes-section">
        <div class="notes-title">Notes</div>
        <ul class="notes-list">
            <li>Bank balances shown are as of the report date</li>
            <li>Monthly trend shows income and expenses recorded each month</li>
            <li>Positive net flow indicates cash surplus, negative indicates deficit</li>
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
                    <div class="signature-line">Accountant</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="confidential-notice">
        This document is confidential and intended for internal use only.
    </div>
@endsection
