<?php

namespace App\Services\Accounting;

use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\JournalEntry;
use App\Models\Accounting\IncomeRecord;
use App\Models\PaymentTransaction;
use App\Models\Payroll;
use App\Models\Accounting\Expense;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AccountingIntegrationService
{
    protected JournalEntryService $journalService;

    // Default account codes - these should match the seeded Chart of Accounts
    protected const ACCOUNTS = [
        'bank_main' => '1010',
        'fees_receivable' => '1101',
        'fees_income_preschool' => '4001',
        'fees_income_primary' => '4002',
        'fees_income_secondary' => '4003',
        'bus_fees_income' => '4101',
        'salary_expense' => '5001',
        'napsa_expense' => '5003',
        'nhima_expense' => '5004',
        'paye_payable' => '2101',
        'napsa_payable' => '2102',
        'nhima_payable' => '2103',
        'accounts_payable' => '2001',
    ];

    public function __construct(JournalEntryService $journalService)
    {
        $this->journalService = $journalService;
    }

    /**
     * Post a fee payment to the accounting system
     */
    public function postFeePayment(PaymentTransaction $transaction): ?JournalEntry
    {
        if ($transaction->type !== 'payment') {
            return null;
        }

        try {
            $studentFee = $transaction->studentFee;
            $student = $studentFee->student;
            $grade = $studentFee->grade;

            // Determine the income account based on school section
            $incomeAccountCode = self::ACCOUNTS['fees_income_primary'];
            if ($grade) {
                if ($grade->school_section_id === 1) {
                    // Check if pre-school (level 0-2) or primary
                    $incomeAccountCode = $grade->level <= 2
                        ? self::ACCOUNTS['fees_income_preschool']
                        : self::ACCOUNTS['fees_income_primary'];
                } else {
                    $incomeAccountCode = self::ACCOUNTS['fees_income_secondary'];
                }
            }

            $bankAccount = $this->getAccountByCode(self::ACCOUNTS['bank_main']);
            $incomeAccount = $this->getAccountByCode($incomeAccountCode);

            if (!$bankAccount || !$incomeAccount) {
                Log::warning('Accounting integration: Required accounts not found for fee payment', [
                    'transaction_id' => $transaction->id,
                ]);
                return null;
            }

            $lines = [
                [
                    'account_id' => $bankAccount->id,
                    'debit_amount' => $transaction->amount,
                    'credit_amount' => 0,
                    'description' => 'Fee payment received',
                ],
                [
                    'account_id' => $incomeAccount->id,
                    'debit_amount' => 0,
                    'credit_amount' => $transaction->amount,
                    'description' => 'School fees - ' . ($student->name ?? 'Student'),
                ],
            ];

            $entry = $this->journalService->createAndPostEntry([
                'entry_date' => $transaction->transaction_date ?? now(),
                'description' => 'Fee payment: ' . ($student->name ?? 'Student') . ' - ' . $transaction->reference_number,
                'reference_type' => PaymentTransaction::class,
                'reference_id' => $transaction->id,
                'academic_year_id' => $studentFee->academic_year_id,
                'created_by' => $transaction->processed_by ?? auth()->id(),
            ], $lines);

            // Create income record
            IncomeRecord::create([
                'income_date' => $transaction->transaction_date ?? now(),
                'account_id' => $incomeAccount->id,
                'description' => 'School fees payment from ' . ($student->name ?? 'Student'),
                'amount' => $transaction->amount,
                'payment_method' => $transaction->payment_method,
                'reference' => $transaction->reference_number,
                'payer_name' => $student->parentGuardian->name ?? $student->name ?? 'Unknown',
                'payer_contact' => $student->parentGuardian->phone ?? null,
                'source_type' => PaymentTransaction::class,
                'source_id' => $transaction->id,
                'journal_entry_id' => $entry->id,
                'academic_year_id' => $studentFee->academic_year_id,
                'created_by' => $transaction->processed_by ?? auth()->id(),
            ]);

            return $entry;
        } catch (\Exception $e) {
            Log::error('Accounting integration error: Failed to post fee payment', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Post payroll to the accounting system
     */
    public function postPayroll(Payroll $payroll): ?JournalEntry
    {
        if ($payroll->payment_status !== 'paid') {
            return null;
        }

        try {
            $bankAccount = $this->getAccountByCode(self::ACCOUNTS['bank_main']);
            $salaryExpense = $this->getAccountByCode(self::ACCOUNTS['salary_expense']);
            $napsaExpense = $this->getAccountByCode(self::ACCOUNTS['napsa_expense']);
            $nhimaExpense = $this->getAccountByCode(self::ACCOUNTS['nhima_expense']);
            $payePayable = $this->getAccountByCode(self::ACCOUNTS['paye_payable']);
            $napsaPayable = $this->getAccountByCode(self::ACCOUNTS['napsa_payable']);
            $nhimaPayable = $this->getAccountByCode(self::ACCOUNTS['nhima_payable']);

            if (!$bankAccount || !$salaryExpense) {
                Log::warning('Accounting integration: Required accounts not found for payroll', [
                    'payroll_id' => $payroll->id,
                ]);
                return null;
            }

            $deductions = $payroll->deductions ?? [];
            $napsa = $deductions['napsa'] ?? 0;
            $nhima = $deductions['nhima'] ?? 0;
            $paye = $deductions['paye'] ?? 0;

            $employeeName = $payroll->employee->name ?? 'Employee';

            $lines = [
                // Debit salary expense (gross)
                [
                    'account_id' => $salaryExpense->id,
                    'debit_amount' => $payroll->gross_salary,
                    'credit_amount' => 0,
                    'description' => 'Salary expense - ' . $employeeName,
                ],
            ];

            // Employer NAPSA contribution
            if ($napsa > 0 && $napsaExpense) {
                $lines[] = [
                    'account_id' => $napsaExpense->id,
                    'debit_amount' => $napsa,
                    'credit_amount' => 0,
                    'description' => 'Employer NAPSA contribution',
                ];
            }

            // Employer NHIMA contribution
            if ($nhima > 0 && $nhimaExpense) {
                $lines[] = [
                    'account_id' => $nhimaExpense->id,
                    'debit_amount' => $nhima,
                    'credit_amount' => 0,
                    'description' => 'Employer NHIMA contribution',
                ];
            }

            // Credit bank (net pay)
            $lines[] = [
                'account_id' => $bankAccount->id,
                'debit_amount' => 0,
                'credit_amount' => $payroll->net_salary,
                'description' => 'Net salary payment',
            ];

            // Credit PAYE payable
            if ($paye > 0 && $payePayable) {
                $lines[] = [
                    'account_id' => $payePayable->id,
                    'debit_amount' => 0,
                    'credit_amount' => $paye,
                    'description' => 'PAYE payable',
                ];
            }

            // Credit NAPSA payable (employee + employer)
            if ($napsa > 0 && $napsaPayable) {
                $lines[] = [
                    'account_id' => $napsaPayable->id,
                    'debit_amount' => 0,
                    'credit_amount' => $napsa * 2, // Employee + Employer
                    'description' => 'NAPSA payable',
                ];
            }

            // Credit NHIMA payable (employee + employer)
            if ($nhima > 0 && $nhimaPayable) {
                $lines[] = [
                    'account_id' => $nhimaPayable->id,
                    'debit_amount' => 0,
                    'credit_amount' => $nhima * 2, // Employee + Employer
                    'description' => 'NHIMA payable',
                ];
            }

            return $this->journalService->createAndPostEntry([
                'entry_date' => $payroll->payment_date ?? now(),
                'description' => 'Payroll: ' . $employeeName . ' - ' . $payroll->month . '/' . $payroll->year,
                'reference_type' => Payroll::class,
                'reference_id' => $payroll->id,
                'created_by' => auth()->id(),
            ], $lines);
        } catch (\Exception $e) {
            Log::error('Accounting integration error: Failed to post payroll', [
                'payroll_id' => $payroll->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Post expense to the accounting system
     */
    public function postExpense(Expense $expense): ?JournalEntry
    {
        try {
            $expenseAccount = $expense->category->account;
            $bankAccount = $expense->bankAccount?->chartAccount;
            $payableAccount = $this->getAccountByCode(self::ACCOUNTS['accounts_payable']);

            if (!$expenseAccount) {
                Log::warning('Accounting integration: No account linked to expense category', [
                    'expense_id' => $expense->id,
                    'category_id' => $expense->expense_category_id,
                ]);
                return null;
            }

            $lines = [
                [
                    'account_id' => $expenseAccount->id,
                    'debit_amount' => $expense->total_amount,
                    'credit_amount' => 0,
                    'description' => $expense->description,
                ],
            ];

            // Credit based on payment status
            if ($expense->isPaid() && $bankAccount) {
                $lines[] = [
                    'account_id' => $bankAccount->id,
                    'debit_amount' => 0,
                    'credit_amount' => $expense->total_amount,
                    'description' => 'Payment for: ' . $expense->description,
                ];
            } elseif ($payableAccount) {
                $lines[] = [
                    'account_id' => $payableAccount->id,
                    'debit_amount' => 0,
                    'credit_amount' => $expense->total_amount,
                    'description' => 'Payable: ' . ($expense->vendor->name ?? 'Vendor'),
                ];
            }

            $entry = $this->journalService->createAndPostEntry([
                'entry_date' => $expense->expense_date,
                'description' => 'Expense: ' . $expense->expense_number . ' - ' . $expense->description,
                'reference_type' => Expense::class,
                'reference_id' => $expense->id,
                'academic_year_id' => $expense->academic_year_id,
                'created_by' => $expense->created_by ?? auth()->id(),
            ], $lines);

            // Update expense with journal entry ID
            $expense->journal_entry_id = $entry->id;
            $expense->save();

            return $entry;
        } catch (\Exception $e) {
            Log::error('Accounting integration error: Failed to post expense', [
                'expense_id' => $expense->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Reverse a journal entry
     */
    public function reverseEntry(int $journalEntryId): ?JournalEntry
    {
        try {
            return $this->journalService->createReversingEntry($journalEntryId, auth()->id());
        } catch (\Exception $e) {
            Log::error('Accounting integration error: Failed to reverse entry', [
                'journal_entry_id' => $journalEntryId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get account by code
     */
    protected function getAccountByCode(string $code): ?ChartOfAccount
    {
        return ChartOfAccount::where('code', $code)->first();
    }
}
