<?php

namespace App\Filament\Resources\SmsCreditResource\Pages;

use App\Filament\Resources\SmsCreditResource;
use App\Models\SmsCredit;
use App\Models\SmsCreditTransaction;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListSmsCredits extends ListRecords
{
    protected static string $resource = SmsCreditResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            SmsCreditResource\Widgets\SmsCreditOverviewWidget::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Transactions')
                ->badge(SmsCreditTransaction::count()),

            'credits' => Tab::make('Top Ups')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'credit'))
                ->badge(SmsCreditTransaction::where('type', 'credit')->count())
                ->badgeColor('success'),

            'debits' => Tab::make('SMS Sent')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'debit'))
                ->badge(SmsCreditTransaction::where('type', 'debit')->count())
                ->badgeColor('danger'),

            'today' => Tab::make('Today')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('created_at', today()))
                ->badge(SmsCreditTransaction::whereDate('created_at', today())->count()),

            'this_month' => Tab::make('This Month')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year))
                ->badge(SmsCreditTransaction::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)->count()),
        ];
    }
}
