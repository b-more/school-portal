<div class="p-6 text-center">
    <div class="mb-4">
        <h3 class="text-xl font-bold text-gray-800 mb-2">School Fee Payment QR Code</h3>
        <p class="text-sm text-gray-600 mb-1">Print this page on A4 paper</p>
        <p class="text-xs text-gray-500">Distribute to parents for easy mobile money payments</p>
    </div>

    <div class="flex justify-center mb-6">
        <div class="border-4 border-gray-800 p-6 rounded-lg bg-white shadow-lg">
            {!! QrCode::size(250)->generate(url('/pay')) !!}
        </div>
    </div>

    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
        <div class="text-sm text-gray-700 mb-3">
            <p class="font-semibold mb-2">Payment URL:</p>
            <div class="bg-white px-3 py-2 rounded border border-gray-300">
                <code class="text-xs font-mono">{{ url('/pay') }}</code>
            </div>
        </div>
    </div>

    <div class="mt-6 pt-4 border-t border-gray-200">
        <button
            onclick="window.print()"
            class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium shadow-lg"
        >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
            </svg>
            Print QR Code (A4 Size)
        </button>
    </div>
</div>

<style>
@media print {
    @page {
        size: A4 portrait;
        margin: 15mm;
    }

    body * {
        visibility: hidden;
    }

    #printable-area,
    #printable-area * {
        visibility: visible;
    }

    #printable-area {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        padding: 0;
        margin: 0;
    }

    button {
        display: none !important;
    }
}
</style>

<div id="printable-area" style="display: none;">
    <div style="width: 210mm; padding: 15mm; font-family: Arial, sans-serif;">
        <!-- Header with Logo -->
        <div style="text-align: center; margin-bottom: 30px; border-bottom: 3px solid #1e40af; padding-bottom: 20px;">
            <div style="margin-bottom: 15px;">
                <img src="{{ asset('images/logo.png') }}" style="width: 120px; height: auto; margin: 0 auto; display: block;" alt="St. Francis Assisi School Logo">
            </div>
            <h1 style="font-size: 32px; font-weight: bold; color: #1e40af; margin: 10px 0;">St. Francis Assisi School</h1>
            <p style="font-size: 16px; color: #64748b; margin: 5px 0;">Excellence in Education</p>
        </div>

        <!-- Main Content -->
        <div style="text-align: center; margin-bottom: 30px;">
            <h2 style="font-size: 28px; font-weight: bold; color: #334155; margin-bottom: 10px;">Mobile Money Payment Portal</h2>
            <p style="font-size: 14px; color: #64748b; margin-bottom: 25px;">Scan the QR code below to pay school fees conveniently</p>
        </div>

        <!-- QR Code Section -->
        <div style="text-align: center; margin: 40px 0;">
            <div style="display: inline-block; border: 8px solid #1e40af; padding: 25px; background: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border-radius: 12px;">
                {!! QrCode::size(350)->margin(2)->generate(url('/pay')) !!}
            </div>
        </div>

        <!-- Instructions Section -->
        <div style="background: #f1f5f9; padding: 25px; border-radius: 10px; margin-bottom: 25px; border-left: 6px solid #1e40af;">
            <h3 style="font-size: 20px; font-weight: bold; color: #1e40af; margin-bottom: 15px;">📱 How to Pay Using This QR Code:</h3>
            <ol style="margin-left: 25px; font-size: 14px; line-height: 2; color: #334155;">
                <li style="margin-bottom: 8px;"><strong>Scan the QR Code</strong> - Use your phone's camera or mobile money app to scan</li>
                <li style="margin-bottom: 8px;"><strong>Search for Your Child</strong> - Enter your child's Student ID Number or Full Name</li>
                <li style="margin-bottom: 8px;"><strong>Review Fee Information</strong> - Check the term, class, total fees and outstanding balance</li>
                <li style="margin-bottom: 8px;"><strong>Enter Payment Details</strong> - Input your mobile money number and the amount you wish to pay</li>
                <li style="margin-bottom: 8px;"><strong>Authorize Payment</strong> - Follow the prompts on your phone to complete the transaction</li>
                <li style="margin-bottom: 8px;"><strong>Confirmation</strong> - Payment is automatically applied to your child's school account</li>
            </ol>
        </div>

        <!-- Alternative Access -->
        <div style="background: #eff6ff; padding: 20px; border-radius: 10px; margin-bottom: 25px; text-align: center; border: 2px dashed #3b82f6;">
            <p style="font-size: 13px; color: #1e40af; font-weight: bold; margin-bottom: 8px;">Can't scan? Visit this link on your phone:</p>
            <p style="font-size: 16px; font-family: 'Courier New', monospace; color: #1e3a8a; font-weight: bold; background: white; padding: 10px; border-radius: 6px; display: inline-block;">{{ url('/pay') }}</p>
        </div>

        <!-- Payment Methods -->
        <div style="background: #ecfdf5; padding: 20px; border-radius: 10px; margin-bottom: 25px; border-left: 6px solid #10b981;">
            <h3 style="font-size: 18px; font-weight: bold; color: #059669; margin-bottom: 12px;">✓ Accepted Payment Methods:</h3>
            <div style="display: flex; justify-content: center; gap: 30px; font-size: 16px; color: #047857; font-weight: bold;">
                <div style="text-align: center; flex: 1;">
                    <div style="font-size: 24px; margin-bottom: 5px;">📱</div>
                    <div>MTN Mobile Money</div>
                </div>
                <div style="text-align: center; flex: 1;">
                    <div style="font-size: 24px; margin-bottom: 5px;">📱</div>
                    <div>Airtel Money</div>
                </div>
                <div style="text-align: center; flex: 1;">
                    <div style="font-size: 24px; margin-bottom: 5px;">📱</div>
                    <div>Zamtel Kwacha</div>
                </div>
            </div>
        </div>

        <!-- Important Notes -->
        <div style="background: #fef3c7; padding: 20px; border-radius: 10px; margin-bottom: 25px; border-left: 6px solid #f59e0b;">
            <h3 style="font-size: 16px; font-weight: bold; color: #d97706; margin-bottom: 10px;">⚠️ Important Notes:</h3>
            <ul style="margin-left: 20px; font-size: 13px; line-height: 1.8; color: #92400e;">
                <li>This QR code is valid for all students at St. Francis Assisi School</li>
                <li>Payments are processed instantly and applied to your child's account automatically</li>
                <li>You will receive a confirmation message once payment is successful</li>
                <li>For assistance, please contact the school bursar's office</li>
            </ul>
        </div>

        <!-- Footer -->
        <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 2px solid #e2e8f0;">
            <p style="font-size: 12px; color: #64748b; margin-bottom: 5px;">
                <strong>St. Francis Assisi School</strong> | Excellence in Education
            </p>
            <p style="font-size: 11px; color: #94a3b8;">
                For support, contact the school office | &copy; {{ date('Y') }} All Rights Reserved
            </p>
        </div>
    </div>
</div>

<script>
// Show print area when printing
window.onbeforeprint = function() {
    document.getElementById('printable-area').style.display = 'block';
};
window.onafterprint = function() {
    document.getElementById('printable-area').style.display = 'none';
};
</script>
