<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FeeStructureResource\Pages;
use App\Filament\Resources\FeeStructureResource\RelationManagers;
use App\Models\FeeStructure;
use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\Grade;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;

class FeeStructureResource extends Resource
{
    protected static ?string $model = FeeStructure::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationGroup = 'Finance Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Academic Period')
                    ->schema([
                        Forms\Components\Select::make('academic_year_id')
                            ->label('Academic Year')
                            ->options(function () {
                                return AcademicYear::query()
                                    ->orderBy('name', 'desc')
                                    ->get()
                                    ->pluck('name', 'id');
                            })
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->placeholder('e.g. 2025'),
                                Forms\Components\DatePicker::make('start_date')
                                    ->required()
                                    ->default(now()->startOfYear()),
                                Forms\Components\DatePicker::make('end_date')
                                    ->required()
                                    ->default(now()->endOfYear()),
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Set as Current Academic Year')
                                    ->helperText('Only one academic year can be active at a time')
                                    ->default(true),
                                Forms\Components\TextInput::make('number_of_terms')
                                    ->numeric()
                                    ->default(3)
                                    ->required()
                                    ->helperText('This will automatically create the specified number of terms'),
                                Forms\Components\Textarea::make('description')
                                    ->rows(2)
                                    ->maxLength(500),
                            ])
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('term_id', null)),

                        Forms\Components\Select::make('term_id')
                            ->label('Term')
                            ->options(function (callable $get) {
                                $academicYearId = $get('academic_year_id');
                                if (!$academicYearId) {
                                    return [];
                                }

                                return Term::where('academic_year_id', $academicYearId)
                                    ->orderBy('name')
                                    ->get()
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->placeholder('e.g. Term 1'),
                                Forms\Components\DatePicker::make('start_date')
                                    ->required()
                                    ->default(now()->startOfQuarter()),
                                Forms\Components\DatePicker::make('end_date')
                                    ->required()
                                    ->default(now()->endOfQuarter()),
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Set as Current Term')
                                    ->default(false)
                                    ->helperText('Only one term per academic year can be active at a time'),
                                Forms\Components\Hidden::make('academic_year_id')
                                    ->default(function (callable $get) {
                                        return $get('../../academic_year_id');
                                    }),
                            ]),

                        Forms\Components\Select::make('grade_id')
                            ->label('Grade')
                            ->options(function () {
                                return Grade::query()
                                    ->orderBy('level')
                                    ->get()
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\Select::make('school_section_id')
                                    ->relationship('schoolSection', 'name')
                                    ->required(),
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->placeholder('e.g. Grade 1'),
                                Forms\Components\TextInput::make('code')
                                    ->required()
                                    ->placeholder('e.g. G1'),
                                Forms\Components\TextInput::make('level')
                                    ->numeric()
                                    ->required(),
                                Forms\Components\TextInput::make('capacity')
                                    ->numeric()
                                    ->default(35)
                                    ->required(),
                                Forms\Components\TextInput::make('breakeven_number')
                                    ->numeric()
                                    ->default(20)
                                    ->required(),
                                Forms\Components\Textarea::make('description')
                                    ->rows(2),
                                Forms\Components\Toggle::make('is_active')
                                    ->default(true),
                            ]),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Fee Details')
                    ->schema([
                        Forms\Components\TextInput::make('basic_fee')
                            ->required()
                            ->numeric()
                            ->prefix('ZMW')
                            ->step(0.01)
                            ->reactive()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, $get) {
                                $set('total_fee', self::calculateTotal($state, $get('additional_charges')));
                            }),

                        Forms\Components\Repeater::make('additional_charges')
                            ->schema([
                                Forms\Components\Select::make('description')
                                    ->options([
                                        'Uniform' => 'Uniform',
                                        'P.E Attire' => 'P.E Attire',
                                        'Jersey' => 'Jersey',
                                        'School Neck Tie' => 'School Neck Tie',
                                        'Exam Fee' => 'Exam Fee',
                                        'Books' => 'Books',
                                        'Computer Lab Fee' => 'Computer Lab Fee',
                                        'Science Lab Fee' => 'Science Lab Fee',
                                        'Transportation' => 'Transportation',
                                        'Boarding' => 'Boarding',
                                        'Development Fund' => 'Development Fund',
                                        'Sports Fee' => 'Sports Fee',
                                        'Library Fee' => 'Library Fee',
                                        'Other' => 'Other (Specify in Description)'
                                    ])
                                    ->required(),
                                Forms\Components\TextInput::make('amount')
                                    ->numeric()
                                    ->required()
                                    ->prefix('ZMW')
                                    ->step(0.01)
                                    ->reactive()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, $get) {
                                        $set('total_fee', self::calculateTotal($get('basic_fee'), $get('additional_charges')));
                                    }),
                            ])
                            ->columns(2)
                            ->reactive()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, $get) {
                                $set('total_fee', self::calculateTotal($get('basic_fee'), $state));
                            })
                            ->collapsible(),

                        Forms\Components\TextInput::make('total_fee')
                            ->required()
                            ->numeric()
                            ->prefix('ZMW')
                            ->step(0.01)
                            ->disabled()
                            ->helperText('Total fee is automatically calculated'),

                        Forms\Components\Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull()
                            ->hint('Additional notes about this fee structure'),

                        Forms\Components\Toggle::make('is_active')
                            ->required()
                            ->default(true)
                            ->helperText('Inactive fee structures will not be available for student fee assignments'),
                    ]),
            ]);
    }

    /**
     * Calculate the total fee based on basic fee and additional charges
     */
    public static function calculateTotal($basicFee, $additionalCharges): float
    {
        // Convert to float and ensure we have a valid numeric value
        $total = is_numeric($basicFee) ? (float) $basicFee : 0;

        if (is_array($additionalCharges)) {
            foreach ($additionalCharges as $charge) {
                if (isset($charge['amount']) && is_numeric($charge['amount'])) {
                    $total += (float) $charge['amount'];
                }
            }
        }

        return round($total, 2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('grade.name')
                    ->label('Grade')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('grade.schoolSection.name')
                    ->label('Section')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('academicYear.name')
                    ->label('Academic Year')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('term.name')
                    ->label('Term')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('basic_fee')
                    ->money('ZMW')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_fee')
                    ->money('ZMW')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('grade_id')
                    ->label('Grade')
                    ->options(function() {
                        return Grade::orderBy('level')
                            ->get()
                            ->pluck('name', 'id')
                            ->toArray();
                    }),
                Tables\Filters\SelectFilter::make('academic_year_id')
                    ->label('Academic Year')
                    ->options(function() {
                        return AcademicYear::orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray();
                    }),
                Tables\Filters\SelectFilter::make('term_id')
                    ->label('Term')
                    ->options(function() {
                        return Term::orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray();
                    }),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All fee structures')
                    ->trueLabel('Active fee structures')
                    ->falseLabel('Inactive fee structures')
                    ->queries(
                        true: fn (Builder $query) => $query->where('is_active', true),
                        false: fn (Builder $query) => $query->where('is_active', false),
                        blank: fn (Builder $query) => $query,
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('generatePdf')
                    ->label('Generate PDF')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->action(function (FeeStructure $record) {
                        // Get related data safely with fallbacks
                        $academicYearName = $record->academicYear->name ?? 'Unknown Academic Year';
                        $termName = $record->term->name ?? 'Unknown Term';
                        $gradeName = $record->grade->name ?? 'Unknown Grade';

                        // Generate PDF
                        $pdf = Pdf::loadView('pdf.fee-structure', [
                            'feeStructure' => $record,
                            'academicYear' => $academicYearName,
                            'term' => $termName,
                            'grade' => $gradeName,
                            'schoolName' => 'St. Francis Of Assisi Private School',
                            'schoolLogo' => public_path('images/logo.png'),
                            'schoolAddress' => 'Plot No 1310/4 East Kamenza, Chililabombwe, Zambia',
                            'schoolContact' => 'Phone: +260 972 266 217, Email: info@stfrancisofassisi.tech'
                        ]);

                        // Save PDF to storage
                        $filename = 'fee-structure-' . $gradeName . '-' . $termName . '-' . $academicYearName . '.pdf';
                        Storage::disk('public')->put('pdfs/fee-structures/' . $filename, $pdf->output());

                        $url = Storage::disk('public')->url('pdfs/fee-structures/' . $filename);

                        // Notify the user
                        Notification::make()
                            ->title('PDF Generated Successfully')
                            ->body('The fee structure PDF has been generated and is ready for download.')
                            ->success()
                            ->send();

                        // Return the PDF for download
                        return response()->streamDownload(
                            fn () => print($pdf->output()),
                            $filename,
                            [
                                'Content-Type' => 'application/pdf',
                            ]
                        );
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->action(function (Builder $query) {
                            $query->update(['is_active' => true]);
                        })
                        ->deselectRecordsAfterCompletion()
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->action(function (Builder $query) {
                            $query->update(['is_active' => false]);
                        })
                        ->deselectRecordsAfterCompletion()
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('generateBulkPdf')
                        ->label('Generate PDFs')
                        ->icon('heroicon-o-document-duplicate')
                        ->color('success')
                        ->action(function (Builder $query) {
                            $feeStructures = $query->get();

                            $count = 0;
                            foreach ($feeStructures as $feeStructure) {
                                // Get related data safely with fallbacks
                                $academicYearName = $feeStructure->academicYear->name ?? 'Unknown Academic Year';
                                $termName = $feeStructure->term->name ?? 'Unknown Term';
                                $gradeName = $feeStructure->grade->name ?? 'Unknown Grade';

                                // Generate PDF for each fee structure
                                $pdf = Pdf::loadView('pdf.fee-structure', [
                                    'feeStructure' => $feeStructure,
                                    'academicYear' => $academicYearName,
                                    'term' => $termName,
                                    'grade' => $gradeName,
                                    'schoolName' => 'St. Francis Of Assisi Private School',
                                    'schoolLogo' => public_path('images/logo.png'),
                                    'schoolAddress' => 'Plot No 1310/4 East Kamenza, Chililabombwe, Zambia',
                                    'schoolContact' => 'Phone: +260 972 266 217, Email: info@stfrancisofassisi.tech'
                                ]);

                                // Save PDF to storage
                                $filename = 'fee-structure-' . $gradeName . '-' . $termName . '-' . $academicYearName . '.pdf';
                                Storage::disk('public')->put('pdfs/fee-structures/' . $filename, $pdf->output());
                                $count++;
                            }

                            // Notify the user
                            Notification::make()
                                ->title('PDFs Generated Successfully')
                                ->body("$count fee structure PDFs have been generated and are available in the storage.")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation()
                        ->modalHeading('Generate Fee Structure PDFs')
                        ->modalSubheading('Are you sure you want to generate PDFs for the selected fee structures?')
                        ->modalButton('Generate'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //RelationManagers\StudentFeesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFeeStructures::route('/'),
            'create' => Pages\CreateFeeStructure::route('/create'),
            'view' => Pages\ViewFeeStructure::route('/{record}'),
            'edit' => Pages\EditFeeStructure::route('/{record}/edit'),
        ];
    }
}
