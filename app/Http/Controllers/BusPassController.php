<?php

namespace App\Http\Controllers;

use App\Models\BusPayment;

class BusPassController extends Controller
{
    /**
     * View bus pass in browser
     */
    public function view(BusPayment $busPayment)
    {
        // Check if user has permission to view this bus pass
        $user = auth()->user();

        if ($user->role_id !== \App\Constants\RoleConstants::ADMIN) {
            // Students can only view their own bus passes
            $student = \App\Models\Student::where('user_id', $user->id)->first();
            if (! $student || $busPayment->student_id !== $student->id) {
                abort(403, 'Unauthorized access to bus pass.');
            }
        }

        // Only allow viewing if payment is made (paid or partial)
        if ($busPayment->payment_status === 'unpaid') {
            abort(403, 'Bus pass is only available for paid tickets.');
        }

        $busPayment->load(['student', 'busFareStructure']);

        return view('bus-passes.view', [
            'busPayment' => $busPayment,
        ]);
    }

    /**
     * Download bus pass as PDF
     */
    public function download(BusPayment $busPayment)
    {
        // Check if user has permission
        $user = auth()->user();

        if ($user->role_id !== \App\Constants\RoleConstants::ADMIN) {
            $student = \App\Models\Student::where('user_id', $user->id)->first();
            if (! $student || $busPayment->student_id !== $student->id) {
                abort(403, 'Unauthorized access to bus pass.');
            }
        }

        if ($busPayment->payment_status === 'unpaid') {
            abort(403, 'Bus pass is only available for paid tickets.');
        }

        $busPayment->load(['student', 'busFareStructure']);

        $pdf = \PDF::loadView('bus-passes.pdf', [
            'busPayment' => $busPayment,
        ]);

        $filename = "bus_pass_{$busPayment->student->student_id_number}_{$busPayment->month}_{$busPayment->year}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Print bus pass
     */
    public function print(BusPayment $busPayment)
    {
        // Check if user has permission
        $user = auth()->user();

        if ($user->role_id !== \App\Constants\RoleConstants::ADMIN) {
            $student = \App\Models\Student::where('user_id', $user->id)->first();
            if (! $student || $busPayment->student_id !== $student->id) {
                abort(403, 'Unauthorized access to bus pass.');
            }
        }

        if ($busPayment->payment_status === 'unpaid') {
            abort(403, 'Bus pass is only available for paid tickets.');
        }

        $busPayment->load(['student', 'busFareStructure']);

        return view('bus-passes.print', [
            'busPayment' => $busPayment,
        ]);
    }
}
