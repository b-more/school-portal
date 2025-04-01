<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait UssdTransactionHandler
{
    protected function processDummyPayment($data)
    {
        try {
            DB::beginTransaction();

            // Find or create appropriate category
            $category = TransactionCategory::firstOrCreate(
                ['name' => $data['giving_type']],
                [
                    'type' => 'income',
                    'description' => 'Auto-created from USSD',
                    'is_active' => true
                ]
            );

            // Create the transaction record
            $transaction = Transaction::create([
                'branch_id' => 1, // Default branch
                'member_id' => $data['member_id'],
                'transaction_type' => strtolower($data['giving_type']),
                'amount' => $data['amount'],
                'payment_method' => 'mobile_money',
                'payment_reference' => $data['reference'],
                'transaction_date' => now(),
                'description' => "{$data['giving_type']} via USSD",
                'category_id' => $category->id,
                'recorded_by' => 1, // System user ID
                'status' => 'completed', // Auto-complete for testing
                'notes' => "USSD Payment - Phone: {$data['phone']}"
            ]);

            // Create USSD giving record
            $giving = UssdGiving::create([
                'phone_number' => $data['phone'],
                'member_id' => $data['member_id'],
                'amount' => $data['amount'],
                'giving_type' => $data['giving_type'],
                'full_name' => $data['full_name'],
                'status' => 'completed', // Auto-complete for testing
                'payment_reference' => $data['reference'],
                'offering_type_id' => $data['offering_type_id'],
                'ussd_session_id' => $data['session_id']
            ]);

            DB::commit();

            // Send confirmation SMS
            $message = $this->generateSuccessMessage($data);
            $this->sendSMS($data['phone'], $message);

            return [
                'success' => true,
                'transaction_id' => $transaction->id,
                'reference' => $data['reference']
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to process dummy payment: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function generateSuccessMessage($data)
    {
        $formatted_amount = number_format($data['amount'], 2);
        return "DIGITAL RECEIPT\n"
            . "His Kingdom Church\n"
            . "-------------\n"
            . "Name: {$data['full_name']}\n"
            . "Type: {$data['giving_type']}\n"
            . "Amount: ZMW {$formatted_amount}\n"
            . "Ref: {$data['reference']}\n"
            . "Date: " . now()->format('d/m/Y H:i') . "\n"
            . "-------------\n"
            . "Payment Status: Completed\n"
            . "Thank you for your giving!";
    }
}
