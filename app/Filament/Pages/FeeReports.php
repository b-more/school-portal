<?php

namespace App\Filament\Pages;

use App\Constants\RoleConstants;
use App\Models\AcademicYear;
use App\Models\ClassSection;
use App\Models\Grade;
use App\Models\StudentFee;
use App\Models\Term;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FeeReports extends Page implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = 'Fee Reports';

    protected static ?string $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.fee-reports';

    public ?array $data = [];

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role_id === RoleConstants::ADMIN ?? false;
    }

    public function mount(): void
    {
        $this->form->fill([
            'academic_year_id' => AcademicYear::where('is_active', true)->first()?->id,
            'term_id' => Term::where('is_active', true)->first()?->id,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('report_type')
                    ->label('Report Type')
                    ->options([
                        'all' => 'All Fees',
                        'by_class' => 'By Class Section',
                        'by_grade' => 'By Grade',
                        'by_payment_status' => 'By Payment Status',
                        'outstanding' => 'Outstanding Balances',
                    ])
                    ->default('all')
                    ->live()
                    ->columnSpan(2),

                Select::make('academic_year_id')
                    ->label('Academic Year')
                    ->options(AcademicYear::orderBy('name', 'desc')->pluck('name', 'id'))
                    ->default(AcademicYear::where('is_active', true)->first()?->id)
                    ->required()
                    ->live(),

                Select::make('term_id')
                    ->label('Term')
                    ->options(function ($get) {
                        $academicYearId = $get('academic_year_id');
                        if (! $academicYearId) {
                            return Term::pluck('name', 'id');
                        }

                        return Term::where('academic_year_id', $academicYearId)->pluck('name', 'id');
                    })
                    ->live(),

                Select::make('grade_id')
                    ->label('Grade')
                    ->options(Grade::orderBy('level')->pluck('name', 'id'))
                    ->searchable()
                    ->visible(fn ($get) => in_array($get('report_type'), ['by_grade', 'by_class']))
                    ->live(),

                Select::make('class_section_id')
                    ->label('Class Section')
                    ->options(function ($get) {
                        $gradeId = $get('grade_id');
                        if (! $gradeId) {
                            return ClassSection::with('grade')->get()->mapWithKeys(function ($section) {
                                return [$section->id => $section->grade->name.' - '.$section->name];
                            });
                        }

                        return ClassSection::where('grade_id', $gradeId)->pluck('name', 'id');
                    })
                    ->searchable()
                    ->visible(fn ($get) => $get('report_type') === 'by_class'),

                Select::make('payment_status')
                    ->label('Payment Status')
                    ->options([
                        'paid' => 'Fully Paid',
                        'partial' => 'Partially Paid',
                        'unpaid' => 'Unpaid',
                        'overpaid' => 'Overpaid',
                    ])
                    ->visible(fn ($get) => $get('report_type') === 'by_payment_status'),
            ])
            ->columns(3)
            ->statePath('data');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('student.student_id_number')
                    ->label('Student ID')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('student.name')
                    ->label('Student Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('grade.name')
                    ->label('Grade')
                    ->sortable()
                    ->default(fn ($record) => $record->student?->grade?->name ?? 'N/A'),

                TextColumn::make('term.name')
                    ->label('Term')
                    ->sortable(),

                TextColumn::make('feeStructure.total_fee')
                    ->label('Total Fee')
                    ->money('ZMW')
                    ->sortable(),

                TextColumn::make('amount_paid')
                    ->label('Amount Paid')
                    ->money('ZMW')
                    ->sortable(),

                TextColumn::make('balance')
                    ->label('Balance')
                    ->money('ZMW')
                    ->sortable()
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'success'),

                TextColumn::make('payment_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'partial' => 'warning',
                        'unpaid' => 'danger',
                        'overpaid' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'paid' => 'Paid',
                        'partial' => 'Partial',
                        'unpaid' => 'Unpaid',
                        'overpaid' => 'Overpaid',
                        default => $state,
                    }),

                TextColumn::make('payment_date')
                    ->label('Last Payment')
                    ->date()
                    ->sortable(),

                TextColumn::make('student.parentGuardian.phone')
                    ->label('Parent Contact')
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('academic_year_id')
                    ->label('Academic Year')
                    ->options(AcademicYear::orderBy('name', 'desc')->pluck('name', 'id')),

                SelectFilter::make('term_id')
                    ->label('Term')
                    ->options(Term::pluck('name', 'id')),

                SelectFilter::make('grade_id')
                    ->label('Grade')
                    ->options(Grade::pluck('name', 'id')),

                SelectFilter::make('payment_status')
                    ->label('Payment Status')
                    ->options([
                        'paid' => 'Paid',
                        'partial' => 'Partially Paid',
                        'unpaid' => 'Unpaid',
                        'overpaid' => 'Overpaid',
                    ]),

                Filter::make('payment_date')
                    ->form([
                        DatePicker::make('paid_from'),
                        DatePicker::make('paid_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['paid_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('payment_date', '>=', $date),
                            )
                            ->when(
                                $data['paid_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('payment_date', '<=', $date),
                            );
                    }),

                Filter::make('outstanding')
                    ->label('Outstanding Only')
                    ->query(fn (Builder $query): Builder => $query->where('balance', '>', 0)),
            ])
            ->actions([
                Action::make('view_details')
                    ->label('Details')
                    ->icon('heroicon-o-eye')
                    ->url(fn (StudentFee $record): string => route('filament.admin.resources.student-fees.view', ['record' => $record])),

                Action::make('payment_history')
                    ->label('History')
                    ->icon('heroicon-o-clock')
                    ->modalContent(fn (StudentFee $record) => view('filament.components.payment-history', [
                        'fee' => $record->load('paymentTransactions'),
                    ]))
                    ->modalHeading(fn (StudentFee $record) => 'Payment History - '.$record->student->name)
                    ->modalWidth('2xl'),

                Action::make('export_pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function (StudentFee $record) {
                        return $this->exportFeePdf($record);
                    }),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\BulkAction::make('export_bulk_pdf')
                    ->label('Export Selected as PDF')
                    ->icon('heroicon-o-document-duplicate')
                    ->action(function ($records) {
                        return $this->exportBulkFeesPdf($records);
                    })
                    ->deselectRecordsAfterCompletion(),
            ])
            ->headerActions([
                Action::make('export_summary')
                    ->label('Export Summary PDF')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->action(function () {
                        return $this->exportFeeSummaryPdf();
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100]);
    }

    protected function getTableQuery(): Builder
    {
        $query = StudentFee::query()
            ->with(['student.grade', 'student.parentGuardian', 'feeStructure', 'term', 'grade'])
            ->withCount('paymentTransactions');

        // Apply academic year and term filters
        if (! empty($this->data['academic_year_id'])) {
            $query->where('academic_year_id', $this->data['academic_year_id']);
        }

        if (! empty($this->data['term_id'])) {
            $query->where('term_id', $this->data['term_id']);
        }

        // Apply report type filters
        $reportType = $this->data['report_type'] ?? 'all';

        switch ($reportType) {
            case 'by_class':
                if (! empty($this->data['class_section_id'])) {
                    $query->whereHas('student', function ($q) {
                        $q->where('class_section_id', $this->data['class_section_id']);
                    });
                }
                break;

            case 'by_grade':
                if (! empty($this->data['grade_id'])) {
                    $query->where('grade_id', $this->data['grade_id']);
                }
                break;

            case 'by_payment_status':
                if (! empty($this->data['payment_status'])) {
                    $query->where('payment_status', $this->data['payment_status']);
                }
                break;

            case 'outstanding':
                $query->where('balance', '>', 0);
                break;

            case 'all':
            default:
                // No additional filtering
                break;
        }

        return $query;
    }

    protected function exportFeePdf(StudentFee $fee)
    {
        try {
            $pdf = Pdf::loadView('pdf.fee-report', [
                'fee' => $fee->load(['student', 'feeStructure', 'term', 'paymentTransactions']),
                'schoolName' => 'St. Francis Of Assisi Private School',
                'reportDate' => now()->format('F d, Y'),
            ]);

            $filename = 'fee-report-'.$fee->student->student_id_number.'-'.now()->format('Y-m-d').'.pdf';

            return response()->streamDownload(
                fn () => print ($pdf->output()),
                $filename,
                ['Content-Type' => 'application/pdf']
            );
        } catch (\Exception $e) {
            Notification::make()
                ->title('Export Failed')
                ->body('Error: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function exportBulkFeesPdf($fees)
    {
        try {
            $feesData = $fees->load(['student', 'feeStructure', 'term', 'grade']);

            $pdf = Pdf::loadView('pdf.fees-list', [
                'fees' => $feesData,
                'schoolName' => 'St. Francis Of Assisi Private School',
                'reportDate' => now()->format('F d, Y'),
                'reportType' => $this->data['report_type'] ?? 'Custom Selection',
                'totalAmount' => $feesData->sum(fn ($fee) => $fee->feeStructure->total_fee),
                'totalPaid' => $feesData->sum('amount_paid'),
                'totalBalance' => $feesData->sum('balance'),
            ]);

            $filename = 'fees-list-'.now()->format('Y-m-d').'.pdf';

            return response()->streamDownload(
                fn () => print ($pdf->output()),
                $filename,
                ['Content-Type' => 'application/pdf']
            );
        } catch (\Exception $e) {
            Notification::make()
                ->title('Export Failed')
                ->body('Error: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function exportFeeSummaryPdf()
    {
        try {
            $fees = $this->getTableQuery()->get()->load(['student', 'feeStructure', 'term', 'grade']);

            $summary = [
                'total_fees' => $fees->sum(fn ($fee) => $fee->feeStructure->total_fee),
                'total_paid' => $fees->sum('amount_paid'),
                'total_balance' => $fees->sum('balance'),
                'paid_count' => $fees->where('payment_status', 'paid')->count(),
                'partial_count' => $fees->where('payment_status', 'partial')->count(),
                'unpaid_count' => $fees->where('payment_status', 'unpaid')->count(),
                'total_students' => $fees->count(),
            ];

            $pdf = Pdf::loadView('pdf.fee-summary', [
                'fees' => $fees,
                'summary' => $summary,
                'schoolName' => 'St. Francis Of Assisi Private School',
                'reportDate' => now()->format('F d, Y'),
                'reportType' => $this->data['report_type'] ?? 'Fee Summary',
                'filters' => $this->data,
            ]);

            $filename = 'fee-summary-'.now()->format('Y-m-d').'.pdf';

            return response()->streamDownload(
                fn () => print ($pdf->output()),
                $filename,
                ['Content-Type' => 'application/pdf']
            );
        } catch (\Exception $e) {
            Notification::make()
                ->title('Export Failed')
                ->body('Error: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }
}
