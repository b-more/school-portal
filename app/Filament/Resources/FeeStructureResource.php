<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FeeStructureResource\Pages;
use App\Filament\Resources\FeeStructureResource\RelationManagers;
use App\Models\FeeStructure;
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
                Forms\Components\Select::make('grade')
                    ->options([
                        'Grade 1' => 'Grade 1',
                        'Grade 2' => 'Grade 2',
                        'Grade 3' => 'Grade 3',
                        'Grade 4' => 'Grade 4',
                        'Grade 5' => 'Grade 5',
                        'Grade 6' => 'Grade 6',
                        'Grade 7' => 'Grade 7',
                        'Grade 8' => 'Grade 8',
                        'Grade 9' => 'Grade 9',
                        'Grade 10' => 'Grade 10',
                        'Grade 11' => 'Grade 11',
                        'Grade 12' => 'Grade 12',
                    ])
                    ->required(),
                Forms\Components\Select::make('term')
                    ->options([
                        'Term 1' => 'Term 1',
                        'Term 2' => 'Term 2',
                        'Term 3' => 'Term 3',
                    ])
                    ->required(),
                Forms\Components\Select::make('academic_year')
                    ->options([
                        '2025-2026' => '2025-2026',
                        '2026-2027' => '2026-2027',
                        '2027-2028' => '2027-2028',
                        '2028-2029' => '2028-2029',
                        '2029-2030' => '2029-2030',
                    ])
                    ->required(),
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
                    }),
                Forms\Components\TextInput::make('total_fee')
                    ->required()
                    ->numeric()
                    ->prefix('ZMW')
                    ->step(0.01)
                    ->disabled()
                    ->helperText('Total fee is automatically calculated'),
                Forms\Components\Textarea::make('description')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_active')
                    ->required()
                    ->default(true),
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
                Tables\Columns\TextColumn::make('grade')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('term')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('academic_year')
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
                Tables\Filters\SelectFilter::make('grade')
                    ->options([
                        'Grade 1' => 'Grade 1',
                        'Grade 2' => 'Grade 2',
                        'Grade 3' => 'Grade 3',
                        'Grade 4' => 'Grade 4',
                        'Grade 5' => 'Grade 5',
                        'Grade 6' => 'Grade 6',
                        'Grade 7' => 'Grade 7',
                        'Grade 8' => 'Grade 8',
                        'Grade 9' => 'Grade 9',
                        'Grade 10' => 'Grade 10',
                        'Grade 11' => 'Grade 11',
                        'Grade 12' => 'Grade 12',
                    ]),
                Tables\Filters\SelectFilter::make('term')
                    ->options([
                        'Term 1' => 'Term 1',
                        'Term 2' => 'Term 2',
                        'Term 3' => 'Term 3',
                    ]),
                Tables\Filters\SelectFilter::make('academic_year')
                    ->options([
                        '2025-2026' => '2025-2026',
                        '2026-2027' => '2026-2027',
                        '2027-2028' => '2027-2028',
                        '2028-2029' => '2028-2029',
                        '2029-2030' => '2029-2030',
                    ]),
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
                        // Generate PDF
                        $pdf = Pdf::loadView('pdf.fee-structure', [
                            'feeStructure' => $record,
                            'schoolName' => 'St. Francis Of Assisi Private School',
                            'schoolLogo' => public_path('images/logo.png'),
                            'schoolAddress' => 'Plot No 1310/4 East Kamenza, Chililabombwe, Zambia',
                            'schoolContact' => 'Phone: +260 972 266 217, Email: info@stfrancisofassisi.tech'
                        ]);

                        // Save PDF to storage
                        $filename = 'fee-structure-' . $record->grade . '-' . $record->term . '-' . $record->academic_year . '.pdf';
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
                                // Generate PDF for each fee structure
                                $pdf = Pdf::loadView('pdf.fee-structure', [
                                    'feeStructure' => $feeStructure,
                                    'schoolName' => 'St. Francis Of Assisi Private School',
                                    'schoolLogo' => public_path('images/logo.png'),
                                    'schoolAddress' => 'Plot No 1310/4 East Kamenza, Chililabombwe, Zambia',
                                    'schoolContact' => 'Phone: +260 972 266 217, Email: info@stfrancisofassisi.tech'
                                ]);

                                // Save PDF to storage
                                $filename = 'fee-structure-' . $feeStructure->grade . '-' . $feeStructure->term . '-' . $feeStructure->academic_year . '.pdf';
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
