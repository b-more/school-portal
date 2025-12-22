<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\SchoolSettings;

class QuickGuideController extends Controller
{
    public function show()
    {
        $settings = SchoolSettings::first();

        return view('quick-guide.index', [
            'settings' => $settings,
        ]);
    }

    public function download()
    {
        $settings = SchoolSettings::first();

        $pdf = Pdf::loadView('quick-guide.pdf', [
            'settings' => $settings,
        ]);

        $pdf->setPaper('a4', 'portrait');

        return $pdf->download('St-Francis-School-Portal-Quick-Guide.pdf');
    }

    public function stream()
    {
        $settings = SchoolSettings::first();

        $pdf = Pdf::loadView('quick-guide.pdf', [
            'settings' => $settings,
        ]);

        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('St-Francis-School-Portal-Quick-Guide.pdf');
    }
}
