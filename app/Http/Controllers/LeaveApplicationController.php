<?php

namespace App\Http\Controllers;

use App\Models\LeaveApplication;
use App\Models\Employee;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveApplicationController extends Controller
{
    /**
     * Generate and stream PDF for leave application
     */
    public function streamPdf(LeaveApplication $leaveApplication)
    {
        // Check authorization
        $this->authorizeAccess($leaveApplication);

        // Load relationships
        $leaveApplication->load([
            'employee',
            'leaveType',
            'coveringEmployee',
            'approver',
            'hodApprover',
            'headApprover',
        ]);

        $pdf = Pdf::loadView('leave-application.pdf', [
            'leaveApplication' => $leaveApplication,
        ]);

        $pdf->setPaper('A4', 'portrait');

        $filename = 'leave-approval-' . $leaveApplication->reference_number . '.pdf';

        return $pdf->stream($filename);
    }

    /**
     * Generate and download PDF for leave application
     */
    public function downloadPdf(LeaveApplication $leaveApplication)
    {
        // Check authorization
        $this->authorizeAccess($leaveApplication);

        // Load relationships
        $leaveApplication->load([
            'employee',
            'leaveType',
            'coveringEmployee',
            'approver',
            'hodApprover',
            'headApprover',
        ]);

        $pdf = Pdf::loadView('leave-application.pdf', [
            'leaveApplication' => $leaveApplication,
        ]);

        $pdf->setPaper('A4', 'portrait');

        $filename = 'leave-approval-' . $leaveApplication->reference_number . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Check if user is authorized to access this leave application
     */
    protected function authorizeAccess(LeaveApplication $leaveApplication)
    {
        $user = Auth::user();

        if (!$user) {
            abort(403, 'Unauthorized access.');
        }

        // Admin can access all
        if ($user->role_id === \App\Constants\RoleConstants::ADMIN) {
            return true;
        }

        // Employee can only access their own leave applications
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee || $leaveApplication->employee_id !== $employee->id) {
            abort(403, 'You are not authorized to access this leave application.');
        }

        return true;
    }
}
