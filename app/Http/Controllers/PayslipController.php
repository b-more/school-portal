<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use Barryvdh\DomPDF\Facade\Pdf;

class PayslipController extends Controller
{
    /**
     * View payslip in browser (HTML version)
     */
    public function view(Payroll $payroll)
    {
        $payroll->load(['employee.salaryGrade', 'employee.leaveBalances']);

        return view('payslips.view', [
            'payroll' => $payroll,
        ]);
    }

    /**
     * Stream payslip PDF in browser
     */
    public function stream(Payroll $payroll)
    {
        $payroll->load(['employee.salaryGrade', 'employee.leaveBalances']);

        $pdf = Pdf::loadView('payslips.pdf', [
            'payroll' => $payroll,
        ]);

        // Set paper size to A4 and optimize for single page
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);

        $filename = $this->generateFilename($payroll);

        return $pdf->stream($filename);
    }

    /**
     * Download payslip as PDF
     */
    public function download(Payroll $payroll)
    {
        $payroll->load(['employee.salaryGrade', 'employee.leaveBalances']);

        $pdf = Pdf::loadView('payslips.pdf', [
            'payroll' => $payroll,
        ]);

        // Set paper size to A4 and optimize for single page
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);

        $filename = $this->generateFilename($payroll);

        return $pdf->download($filename);
    }

    /**
     * Print payslip (PDF stream optimized for printing)
     */
    public function print(Payroll $payroll)
    {
        // Redirect to stream for printing
        return $this->stream($payroll);
    }

    /**
     * Generate consistent filename for payslip
     */
    protected function generateFilename(Payroll $payroll): string
    {
        $employeeId = $payroll->employee->employee_number
            ?? $payroll->employee->employee_id
            ?? $payroll->employee->id;

        $month = strtolower($payroll->month);
        $year = $payroll->year;

        return "payslip_{$employeeId}_{$month}_{$year}.pdf";
    }
}
