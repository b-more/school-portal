<?php

namespace App\Filament\Resources\PaymentTransactionResource\Pages;

use App\Constants\RoleConstants;
use App\Filament\Resources\PaymentTransactionResource;
use App\Filament\Resources\PaymentTransactionResource\Widgets\MonthPaymentsWidget;
use App\Filament\Resources\PaymentTransactionResource\Widgets\Term1PaymentsWidget;
use App\Filament\Resources\PaymentTransactionResource\Widgets\Term2PaymentsWidget;
use App\Filament\Resources\PaymentTransactionResource\Widgets\Term3PaymentsWidget;
use App\Filament\Resources\PaymentTransactionResource\Widgets\TermPaymentsWidget;
use App\Filament\Resources\PaymentTransactionResource\Widgets\TodayPaymentsWidget;
use App\Models\AcademicYear;
use App\Models\Term;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ListPaymentTransactions extends ListRecords
{
    protected static string $resource = PaymentTransactionResource::class;

    public function getTitle(): string
    {
        $user = Auth::user();
        $isStudent = $user && $user->role_id === RoleConstants::STUDENT;

        return $isStudent ? 'My Payment History' : 'Payment Transactions';
    }

    public function getSubheading(): ?string
    {
        $user = Auth::user();
        $isStudent = $user && $user->role_id === RoleConstants::STUDENT;

        if ($isStudent) {
            return 'View all your payment transactions and download receipts';
        }

        $academicYear = Cache::remember('current_academic_year_name', 3600, function () {
            return AcademicYear::where('is_current', true)->value('name');
        });

        $term = Cache::remember('current_term_name', 3600, function () {
            return Term::where('is_current', true)->value('name');
        });

        $context = collect([$term, $academicYear])->filter()->implode(' — ');

        return $context
            ? "Showing payments for: {$context}"
            : 'Track and manage all payment transactions received';
    }

    protected function getHeaderActions(): array
    {
        return [
            // No create action - payments are created through StudentFeeResource
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // Admin widgets (hidden from students) - Row 1: Today | Month | Term, Row 2: Outstanding
            TodayPaymentsWidget::class,
            MonthPaymentsWidget::class,
            TermPaymentsWidget::class,

            // Student widgets (hidden from admins) - 2 columns layout
            Term1PaymentsWidget::class,
            Term2PaymentsWidget::class,
            Term3PaymentsWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 3; // Display in 3 columns for admin widgets row layout
    }
}
