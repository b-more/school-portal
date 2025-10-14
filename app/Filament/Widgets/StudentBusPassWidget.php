<?php

namespace App\Filament\Widgets;

use App\Models\BusPayment;
use App\Models\Student;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class StudentBusPassWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        $user = auth()->user();
        $student = Student::where('user_id', $user->id)->first();

        return $table
            ->heading('My Bus Passes')
            ->description('View and download your active bus passes')
            ->query(
                BusPayment::query()
                    ->where('student_id', $student?->id)
                    ->whereIn('payment_status', ['paid', 'partial'])
                    ->where('year', '>=', now()->year - 1)
                    ->with(['busFareStructure'])
                    ->orderBy('year', 'desc')
                    ->orderBy('month', 'desc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('busFareStructure.route_name')
                    ->label('Route')
                    ->icon('heroicon-m-map-pin')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('month')
                    ->label('Valid For')
                    ->formatStateUsing(fn ($record) => $record->month ? "{$record->month} {$record->year}" : "Full Term {$record->year}")
                    ->badge(),

                Tables\Columns\TextColumn::make('amount_paid')
                    ->label('Amount Paid')
                    ->money('ZMW')
                    ->color('success'),

                Tables\Columns\BadgeColumn::make('payment_status')
                    ->label('Status')
                    ->colors([
                        'success' => 'paid',
                        'warning' => 'partial',
                    ])
                    ->icons([
                        'heroicon-o-check-circle' => 'paid',
                        'heroicon-o-clock' => 'partial',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('view_pass')
                    ->label('View Pass')
                    ->icon('heroicon-o-ticket')
                    ->color('primary')
                    ->url(fn (BusPayment $record) => route('bus-passes.view', $record))
                    ->openUrlInNewTab(),
            ])
            ->emptyStateHeading('No Active Bus Passes')
            ->emptyStateDescription('You don\'t have any active bus passes yet.')
            ->emptyStateIcon('heroicon-o-ticket');
    }
}
