<?php

namespace App\Filament\Widgets;

use App\Constants\RoleConstants;
use App\Models\SmsCredit;
use Filament\Widgets\Widget;

class SmsBalanceWidget extends Widget
{
    protected static string $view = 'filament.widgets.sms-balance-widget';

    protected int | string | array $columnSpan = 1;

    protected static ?int $sort = 5;

    public static function canView(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        return $user->role_id === RoleConstants::ADMIN;
    }

    protected function getViewData(): array
    {
        $credit = SmsCredit::first();

        if (!$credit) {
            return [
                'balance' => 0,
                'estimatedSms' => 0,
                'isLow' => true,
                'isActive' => false,
                'costPerSms' => 0.50,
            ];
        }

        $estimatedSms = $credit->cost_per_sms > 0
            ? floor($credit->balance / $credit->cost_per_sms)
            : 0;

        return [
            'balance' => $credit->balance,
            'estimatedSms' => $estimatedSms,
            'isLow' => $credit->isBalanceLow(),
            'isActive' => $credit->is_active,
            'costPerSms' => $credit->cost_per_sms,
        ];
    }
}
