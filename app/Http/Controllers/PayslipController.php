<?php

namespace App\Http\Controllers;

use App\Models\Payroll;

class PayslipController extends Controller
{
    /**
     * View payslip in browser
     */
    public function view(Payroll $payroll)
    {
        $payroll->load('employee');

        return view('payslips.view', [
            'payroll' => $payroll,
        ]);
    }

    /**
     * Download payslip as PDF
     */
    public function download(Payroll $payroll)
    {
        $payroll->load('employee');

        $pdf = \PDF::loadView('payslips.pdf', [
            'payroll' => $payroll,
        ]);

        $filename = "payslip_{$payroll->employee->employee_id}_{$payroll->month}_{$payroll->year}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Print payslip (same as view but optimized for printing)
     */
    public function print(Payroll $payroll)
    {
        $payroll->load('employee');

        return view('payslips.print', [
            'payroll' => $payroll,
        ]);
    }
}
