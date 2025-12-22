<?php

/**
 * Generate Accounts Module User Guide PDF
 *
 * Run this script from the project root:
 * php generate-accounts-guide-pdf.php
 */

require __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\SchoolSettings;
use Carbon\Carbon;

echo "Generating Accounts Module User Guide PDF...\n";

try {
    $settings = SchoolSettings::first();
    $generatedAt = Carbon::now();

    $pdf = Pdf::loadView('guides.accounts-module-guide', [
        'settings' => $settings,
        'generatedAt' => $generatedAt,
    ]);

    $pdf->setPaper('A4', 'portrait');

    // Set options for better rendering
    $pdf->setOptions([
        'isHtml5ParserEnabled' => true,
        'isRemoteEnabled' => true,
        'defaultFont' => 'DejaVu Sans',
    ]);

    // Save to project root
    $outputPath = __DIR__ . '/Accounts-Module-User-Guide.pdf';
    $pdf->save($outputPath);

    $fileSize = filesize($outputPath);
    echo "PDF generated successfully!\n";
    echo "File: {$outputPath}\n";
    echo "Size: " . number_format($fileSize) . " bytes\n";

} catch (Exception $e) {
    echo "Error generating PDF: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
