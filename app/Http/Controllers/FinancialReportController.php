<?php

namespace App\Http\Controllers;

use App\Models\SchoolSettings;
use App\Services\Accounting\FinancialReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FinancialReportController extends Controller
{
    protected FinancialReportService $reportService;

    public function __construct(FinancialReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function incomeExpenseReport(Request $request)
    {
        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : Carbon::now()->startOfMonth();
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date')) : Carbon::now()->endOfMonth();

        $comparison = $this->reportService->getIncomeExpenseComparison($startDate, $endDate);
        $incomeSummary = $this->reportService->getIncomeSummary($startDate, $endDate);
        $expenseSummary = $this->reportService->getExpenseSummary($startDate, $endDate);

        $settings = SchoolSettings::first();

        $pdf = Pdf::loadView('pdf.financial.income-expense', [
            'settings' => $settings,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'comparison' => $comparison,
            'incomeSummary' => $incomeSummary,
            'expenseSummary' => $expenseSummary,
            'generatedAt' => now(),
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('income-expense-report-' . $startDate->format('Y-m-d') . '-to-' . $endDate->format('Y-m-d') . '.pdf');
    }

    public function cashFlowReport(Request $request)
    {
        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : Carbon::now()->startOfMonth();
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date')) : Carbon::now()->endOfMonth();

        $bankBalances = $this->reportService->getBankBalances();
        $monthlyTrend = $this->reportService->getMonthlyTrend(Carbon::now()->year);

        $settings = SchoolSettings::first();

        $pdf = Pdf::loadView('pdf.financial.cash-flow', [
            'settings' => $settings,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'bankBalances' => $bankBalances,
            'monthlyTrend' => $monthlyTrend,
            'generatedAt' => now(),
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('cash-flow-report-' . $startDate->format('Y-m-d') . '-to-' . $endDate->format('Y-m-d') . '.pdf');
    }

    public function expenseDetailReport(Request $request)
    {
        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : Carbon::now()->startOfMonth();
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date')) : Carbon::now()->endOfMonth();

        $expenseSummary = $this->reportService->getExpenseSummary($startDate, $endDate);

        $settings = SchoolSettings::first();

        $pdf = Pdf::loadView('pdf.financial.expense-detail', [
            'settings' => $settings,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'expenseSummary' => $expenseSummary,
            'generatedAt' => now(),
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('expense-detail-report-' . $startDate->format('Y-m-d') . '-to-' . $endDate->format('Y-m-d') . '.pdf');
    }

    public function incomeDetailReport(Request $request)
    {
        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : Carbon::now()->startOfMonth();
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date')) : Carbon::now()->endOfMonth();

        $incomeSummary = $this->reportService->getIncomeSummary($startDate, $endDate);

        $settings = SchoolSettings::first();

        $pdf = Pdf::loadView('pdf.financial.income-detail', [
            'settings' => $settings,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'incomeSummary' => $incomeSummary,
            'generatedAt' => now(),
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('income-detail-report-' . $startDate->format('Y-m-d') . '-to-' . $endDate->format('Y-m-d') . '.pdf');
    }

    public function payablesReport(Request $request)
    {
        $payables = $this->reportService->getOutstandingPayables();

        $settings = SchoolSettings::first();

        $pdf = Pdf::loadView('pdf.financial.payables', [
            'settings' => $settings,
            'payables' => $payables,
            'generatedAt' => now(),
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('outstanding-payables-report-' . now()->format('Y-m-d') . '.pdf');
    }
}
