<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ResultResource\Pages;
use App\Filament\Resources\ResultResource\RelationManagers;
use App\Models\Result;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Employee;
use App\Models\Homework;
use App\Models\SmsLog;
use App\Services\SmsService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class ResultResource extends Resource
{
    protected static ?string $model = Result::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Academic Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Student & Subject')
                    ->schema([
                        Forms\Components\Select::make('student_id')
                            ->relationship('student', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive(),
                        Forms\Components\Select::make('subject_id')
                            ->relationship('subject', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive(),
                    ])->columns(2),

                Forms\Components\Section::make('Exam Information')
                    ->schema([
                        Forms\Components\Select::make('exam_type')
                            ->options([
                                'mid-term' => 'Mid-Term',
                                'final' => 'Final',
                                'quiz' => 'Quiz',
                                'assignment' => 'Assignment',
                            ])
                            ->required()
                            ->reactive(),

                        Forms\Components\Select::make('homework_id')
                            ->label('Linked Homework')
                            ->options(function (callable $get) {
                                $studentId = $get('student_id');
                                $subjectId = $get('subject_id');

                                if (!$studentId || !$subjectId) {
                                    return [];
                                }

                                // Get student grade
                                $student = Student::find($studentId);
                                if (!$student) {
                                    return [];
                                }

                                return Homework::where('subject_id', $subjectId)
                                    ->where('grade', $student->grade)
                                    ->orderBy('title')
                                    ->pluck('title', 'id')
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->visible(fn (callable $get) => $get('exam_type') === 'assignment')
                            ->required(fn (callable $get) => $get('exam_type') === 'assignment')
                            ->helperText('Select the homework assignment this result is based on'),

                        Forms\Components\TextInput::make('marks')
                            ->numeric()
                            ->required()
                            ->step(0.01)
                            ->minValue(0)
                            ->maxValue(100),

                        Forms\Components\TextInput::make('grade')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('term')
                            ->options([
                                'first' => 'First Term',
                                'second' => 'Second Term',
                                'third' => 'Third Term',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('year')
                            ->numeric()
                            ->required()
                            ->default(date('Y')),
                    ])->columns(2),

                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Textarea::make('comment')
                            ->maxLength(65535)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('recorded_by')
                            ->relationship('recordedBy', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Toggle::make('notify_parent')
                            ->label('Send SMS notification to parent')
                            ->default(true)
                            ->reactive(),

                        Forms\Components\Textarea::make('sms_message')
                            ->label('Custom SMS Message (optional)')
                            ->placeholder('Leave empty to use the default message template')
                            ->helperText('Default template includes student name, subject, and result details')
                            ->visible(fn (callable $get) => $get('notify_parent'))
                            ->maxLength(160),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subject.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('exam_type')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('homework.title')
                    ->label('Homework')
                    ->visible(fn ($record) => $record && $record->exam_type === 'assignment' && $record->homework_id)
                    ->searchable(),
                Tables\Columns\TextColumn::make('marks')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('grade')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('term')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('year')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('recordedBy.name')
                    ->sortable()
                    ->label('Recorded By')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('subject')
                    ->relationship('subject', 'name'),
                Tables\Filters\SelectFilter::make('exam_type')
                    ->options([
                        'mid-term' => 'Mid-Term',
                        'final' => 'Final',
                        'quiz' => 'Quiz',
                        'assignment' => 'Assignment',
                    ]),
                Tables\Filters\SelectFilter::make('term')
                    ->options([
                        'first' => 'First Term',
                        'second' => 'Second Term',
                        'third' => 'Third Term',
                    ]),
                Tables\Filters\Filter::make('year')
                    ->form([
                        Forms\Components\TextInput::make('year'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder =>
                        $query->when($data['year'], fn($q) => $q->where('year', $data['year']))
                    ),
                Tables\Filters\Filter::make('homework')
                    ->label('Has Linked Homework')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('homework_id'))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('viewHomework')
                    ->label('View Homework')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->url(fn (Result $record) => $record && $record->homework_id
                        ? route('filament.admin.resources.homeworks.view', ['record' => $record->homework_id])
                        : null)
                    ->visible(fn (Result $record) => $record && $record->exam_type === 'assignment' && $record->homework_id)
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Send SMS notification to parent about the result
     */
    public static function sendResultNotification(Result $result): void
    {
        // Make sure the result is not null
        if (!$result) {
            Notification::make()
                ->title('SMS Not Sent')
                ->body('Invalid result record.')
                ->warning()
                ->send();
            return;
        }

        // Get the student and parent
        $student = $result->student;
        if (!$student || !$student->parentGuardian) {
            Notification::make()
                ->title('SMS Not Sent')
                ->body('No parent/guardian found for this student.')
                ->warning()
                ->send();
            return;
        }

        $parent = $student->parentGuardian;
        if (!$parent->phone) {
            Notification::make()
                ->title('SMS Not Sent')
                ->body('No phone number found for the parent/guardian.')
                ->warning()
                ->send();
            return;
        }

        try {
            // Format the message based on the result type
            $customMessage = $result->sms_message;

            if (empty($customMessage)) {
                // Create message based on result type
                $subjectName = $result->subject->name ?? 'N/A';
                $examType = ucfirst($result->exam_type);

                // Add homework title if it's an assignment
                $homeworkInfo = '';
                if ($result->exam_type === 'assignment' && $result->homework) {
                    $homeworkInfo = " - {$result->homework->title}";
                }

                $message = "Dear {$parent->name}, your child {$student->name} has received a result for {$subjectName} {$examType}{$homeworkInfo}. Grade: {$result->grade}, Marks: {$result->marks}%. Please log in to the parent portal for details.";
            } else {
                $message = $customMessage;
            }

            // Format phone number and send SMS
            $formattedPhone = self::formatPhoneNumber($parent->phone);
            SmsService::send($message, $formattedPhone);

            // Log the SMS
            SmsLog::create([
                'recipient' => $formattedPhone,
                'message' => $message,
                'status' => 'sent',
                'message_type' => 'result_notification',
                'reference_id' => $result->id,
                'cost' => 0.5, // Assuming cost per SMS
                'sent_by' => auth()->id(),
            ]);

            // Show success notification
            Notification::make()
                ->title('Result Notification Sent')
                ->body("SMS notification sent to {$parent->name} at {$formattedPhone}")
                ->success()
                ->send();

        } catch (\Exception $e) {
            // Log error
            Log::error('Failed to send result notification', [
                'result_id' => $result->id,
                'student_id' => $student->id,
                'parent_id' => $parent->id,
                'error' => $e->getMessage()
            ]);

            // Log the failed SMS
            SmsLog::create([
                'recipient' => $parent->phone,
                'message' => $message ?? 'Result notification',
                'status' => 'failed',
                'message_type' => 'result_notification',
                'reference_id' => $result->id,
                'error_message' => $e->getMessage(),
                'sent_by' => auth()->id(),
            ]);

            // Show error notification
            Notification::make()
                ->title('SMS Notification Failed')
                ->body("Could not send result notification: {$e->getMessage()}")
                ->danger()
                ->send();
        }
    }

    public static function shouldRegisterNavigation(): bool
{
    return false;
}

    /**
     * Format phone number to ensure it has the country code
     */
    protected static function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove any non-numeric characters
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

        // Check if number already has country code (260 for Zambia)
        if (substr($phoneNumber, 0, 3) === '260') {
            // Number already has country code
            return $phoneNumber;
        }

        // If starting with 0, replace with country code
        if (substr($phoneNumber, 0, 1) === '0') {
            return '260' . substr($phoneNumber, 1);
        }

        // If number doesn't have country code, add it
        if (strlen($phoneNumber) === 9) {
            return '260' . $phoneNumber;
        }

        return $phoneNumber;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListResults::route('/'),
            'create' => Pages\CreateResult::route('/create'),
            'view' => Pages\ViewResult::route('/{record}'),
            'edit' => Pages\EditResult::route('/{record}/edit'),
        ];
    }
}
