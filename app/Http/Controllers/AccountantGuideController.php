<?php

namespace App\Http\Controllers;

use App\Models\SchoolSettings;
use App\Services\ImprovedAccountantGuideDocxService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class AccountantGuideController extends Controller
{
    public function show()
    {
        $settings = SchoolSettings::first();
        $generatedAt = Carbon::now();

        $pdf = Pdf::loadView('guides.accountant-guide', [
            'settings' => $settings,
            'generatedAt' => $generatedAt,
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('Accountant-User-Guide.pdf');
    }

    public function download()
    {
        $settings = SchoolSettings::first();
        $generatedAt = Carbon::now();

        $pdf = Pdf::loadView('guides.accountant-guide', [
            'settings' => $settings,
            'generatedAt' => $generatedAt,
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('St-Francis-Accountant-User-Guide.pdf');
    }

    /**
     * Download the Accountant User Guide as a Word document (DOCX)
     */
    public function downloadDocx()
    {
        $service = new ImprovedAccountantGuideDocxService();

        // Generate the document to a temporary file
        $tempPath = storage_path('app/temp');
        if (!is_dir($tempPath)) {
            mkdir($tempPath, 0755, true);
        }

        $filename = 'St-Francis-Accountant-User-Guide-' . now()->format('Y-m-d') . '.docx';
        $filepath = $tempPath . '/' . $filename;

        $service->save($filepath);

        return response()->download($filepath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }
}
