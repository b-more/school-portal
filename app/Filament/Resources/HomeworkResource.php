<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HomeworkResource\Pages;
use App\Models\Homework;
use App\Models\Subject;
use App\Models\Student;
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
use Filament\Support\Colors\Color;

class HomeworkResource extends Resource
{
    protected static ?string $model = Homework::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Academic Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Homework Details')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('homework_file')
                            ->label('Main Homework Document (PDF)')
                            ->helperText('Upload the main homework document that students will need to complete')
                            ->acceptedFileTypes(['application/pdf'])
                            ->directory('homework-files')
                            ->maxSize(10240) // 10MB
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('file_attachment')
                            ->label('Additional Resources (Optional)')
                            ->helperText('Upload any additional resources or reference materials')
                            ->directory('homework-resources')
                            //->multiple()
                            ->maxSize(10240) // 10MB
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Assignment Information')
                    ->schema([
                        Forms\Components\Select::make('subject_id')
                            ->label('Subject')
                            ->options(Subject::query()->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        Forms\Components\TextInput::make('grade')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('due_date')
                            ->label('Due Date')
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'completed' => 'Completed',
                            ])
                            ->required()
                            ->default('active'),
                        Forms\Components\Select::make('assigned_by')
                            ->relationship('assignedBy', 'name')
                            ->searchable(),
                            //->required(),
                        Forms\Components\TextInput::make('max_score')
                            ->label('Maximum Score')
                            ->numeric()
                            ->default(100)
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Submission Settings')
                    ->schema([
                        Forms\Components\DateTimePicker::make('submission_start')
                            ->label('Open Submission From')
                            ->helperText('When can students start submitting their homework?')
                            ->required(),
                        Forms\Components\DateTimePicker::make('submission_end')
                            ->label('Submission Deadline')
                            ->helperText('When is the regular submission deadline?')
                            ->required()
                            ->after('submission_start'),
                        Forms\Components\Toggle::make('allow_late_submission')
                            ->label('Allow Late Submissions')
                            ->default(false)
                            ->reactive(),
                        Forms\Components\DateTimePicker::make('late_submission_deadline')
                            ->label('Late Submission Deadline')
                            ->helperText('Final deadline after which no submissions will be accepted')
                            ->required(fn (callable $get) => $get('allow_late_submission'))
                            ->visible(fn (callable $get) => $get('allow_late_submission'))
                            ->after('submission_end'),
                        Forms\Components\Textarea::make('submission_instructions')
                            ->label('Submission Instructions')
                            ->helperText('Provide detailed instructions on how to complete and submit the homework')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Parent Notification')
                    ->schema([
                        Forms\Components\Toggle::make('notify_parents')
                            ->label('Send SMS notifications to parents')
                            ->default(true)
                            ->helperText('This will automatically send SMS notifications to parents/guardians of students in this grade'),
                        Forms\Components\Textarea::make('sms_message')
                            ->label('Custom SMS Message (optional)')
                            ->placeholder('Leave empty to use the default message template')
                            ->helperText('Default template includes homework title, subject, and due date')
                            ->visible(fn (callable $get) => $get('notify_parents'))
                            ->maxLength(160),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('subject.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('grade')
                    ->sortable(),
                Tables\Columns\TextColumn::make('assignedBy.name')
                    ->sortable()
                    ->label('Assigned By'),
                Tables\Columns\TextColumn::make('due_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('submission_end')
                    ->dateTime()
                    ->label('Submission Deadline')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'active',
                        'success' => 'completed',
                    ]),
                Tables\Columns\TextColumn::make('submissions_count')
                    ->counts('submissions')
                    ->label('Submissions')
                    ->color(Color::Blue),
                Tables\Columns\TextColumn::make('sms_sent_count')
                    ->counts('smsLogs')
                    ->label('SMS Sent')
                    ->tooltip('Number of SMS notifications sent'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('subject')
                    ->relationship('subject', 'name'),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'completed' => 'Completed',
                    ]),
                Tables\Filters\Filter::make('due_date')
                    ->form([
                        Forms\Components\DatePicker::make('due_from'),
                        Forms\Components\DatePicker::make('due_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['due_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('due_date', '>=', $date),
                            )
                            ->when(
                                $data['due_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('due_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('sendNotifications')
                    ->label('Send SMS Notifications')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('warning')
                    ->action(function (Homework $record) {
                        self::sendSmsNotifications($record);
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Send SMS Notifications')
                    ->modalDescription('This will send SMS notifications to all parents/guardians of students in this grade. Are you sure you want to continue?')
                    ->modalSubmitActionLabel('Yes, Send Notifications')
                    ->visible(fn (Homework $record) => $record->status === 'active'),
                Tables\Actions\Action::make('viewSubmissions')
                    ->label('View Submissions')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('success')
                    ->url(fn (Homework $record) => route('filament.admin.resources.homework-submissions.index', ['tableFilters[homework][value]' => $record->id])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('mark_completed')
                        ->action(function (Builder $query) {
                            $query->update(['status' => 'completed']);
                        })
                        ->deselectRecordsAfterCompletion()
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('send_bulk_notifications')
                        ->label('Send SMS Notifications')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('warning')
                        ->action(function (Builder $query) {
                            $homeworks = $query->where('status', 'active')->get();

                            foreach ($homeworks as $homework) {
                                self::sendSmsNotifications($homework);
                            }

                            Notification::make()
                                ->title('SMS Notifications Queued')
                                ->body('SMS notifications are being sent to parents.')
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Send Bulk SMS Notifications')
                        ->modalDescription('This will send SMS notifications for all selected active homeworks. Are you sure you want to continue?')
                        ->modalSubmitActionLabel('Yes, Send All Notifications'),
                ]),
            ]);
    }

    public static function sendSmsNotifications(Homework $homework)
    {
        // Find all students in this grade
        $students = Student::where('grade', $homework->grade)
            ->where('enrollment_status', 'active')
            ->with('parentGuardian')
            ->get();

        $subject = $homework->subject->name ?? 'N/A';
        $successCount = 0;
        $failedCount = 0;

        foreach ($students as $student) {
            if (!$student->parentGuardian || !$student->parentGuardian->phone) {
                continue;
            }

            // Format message
            $customMessage = $homework->sms_message;

            if (empty($customMessage)) {
                // Create a portal URL for parents to access the homework
                $portalUrl = route('filament.parent.pages.view-homework', ['homeworkId' => $homework->id]);

                $message = "Dear Parent/Guardian, new homework assigned for {$student->name} in {$subject}. Title: {$homework->title}. Due: " . $homework->due_date->format('d/m/Y') . ". Check the parent portal.";
            } else {
                $message = $customMessage;
            }

            try {
                // Send SMS
                SmsService::send($message, $student->parentGuardian->phone);

                // Log the SMS
                SmsLog::create([
                    'recipient' => $student->parentGuardian->phone,
                    'message' => $message,
                    'status' => 'sent',
                    'message_type' => 'homework_notification',
                    'reference_id' => $homework->id,
                    'cost' => 0.5, // Assuming cost per SMS, adjust as needed
                    'sent_by' => auth()->id(),
                ]);

                $successCount++;
            } catch (\Exception $e) {
                // Log the error
                Log::error('Failed to send homework notification SMS', [
                    'student_id' => $student->id,
                    'parent_phone' => $student->parentGuardian->phone,
                    'homework_id' => $homework->id,
                    'error' => $e->getMessage()
                ]);

                // Log the failed SMS
                SmsLog::create([
                    'recipient' => $student->parentGuardian->phone,
                    'message' => $message,
                    'status' => 'failed',
                    'message_type' => 'homework_notification',
                    'reference_id' => $homework->id,
                    'error_message' => $e->getMessage(),
                    'sent_by' => auth()->id(),
                ]);

                $failedCount++;
            }
        }

        // Display notification with results
        if ($successCount > 0 || $failedCount > 0) {
            $message = "SMS Notifications: {$successCount} sent successfully";
            if ($failedCount > 0) {
                $message .= ", {$failedCount} failed";
            }

            Notification::make()
                ->title($message)
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('No eligible parents found')
                ->body('No SMS notifications were sent because no eligible parents were found for this grade.')
                ->warning()
                ->send();
        }
    }

    public static function getRelations(): array
    {
        return [
            // We'll define the Submissions relation manager
            RelationManagers\SubmissionsRelationManager::class,
            RelationManagers\SmsLogsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHomework::route('/'),
            'create' => Pages\CreateHomework::route('/create'),
            'view' => Pages\ViewHomework::route('/{record}'),
            'edit' => Pages\EditHomework::route('/{record}/edit'),
        ];
    }
}
