<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HomeworkResource\Pages;
use App\Models\Homework;
use App\Models\Subject;
use App\Models\Grade;
use App\Models\Student;
use App\Models\ParentGuardian;
use App\Models\Employee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class HomeworkResource extends Resource
{
    protected static ?string $model = Homework::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Teaching';
    protected static ?string $navigationLabel = 'Homework';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        $user = Auth::user();
        $teacher = Employee::where('user_id', $user->id)->first();

        // Get subjects for the teacher
        $subjectOptions = [];
        if ($teacher) {
            $subjectOptions = $teacher->subjects()->pluck('name', 'id')->toArray();
        } else if ($user->hasRole('Admin')) {
            $subjectOptions = Subject::pluck('name', 'id')->toArray();
        }

        // Get grades for the teacher
        $gradeOptions = [];
        if ($teacher) {
            // Get grades from teacher's classes
            $gradeOptions = $teacher->classes()
                ->with('grade')
                ->get()
                ->pluck('grade.name', 'grade.id')
                ->unique()
                ->toArray();
        } else if ($user->hasRole('Admin')) {
            $gradeOptions = Grade::where('is_active', true)->pluck('name', 'id')->toArray();
        }

        return $form
            ->schema([
                Forms\Components\Section::make('Homework Information')
                    ->description('Upload homework for students to access')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('subject_id')
                            ->label('Subject')
                            ->options($subjectOptions)
                            ->searchable()
                            ->required(),

                        Forms\Components\Select::make('grade_id')
                            ->label('Grade')
                            ->options($gradeOptions)
                            ->required(),

                        Forms\Components\DatePicker::make('due_date')
                            ->label('Due Date')
                            ->required()
                            ->default(now()->addWeek()),

                        Forms\Components\Textarea::make('description')
                            ->label('Instructions')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('homework_file')
                            ->label('Homework Document')
                            ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                            ->directory('homework-files')
                            ->maxSize(10240) // 10MB
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\Hidden::make('assigned_by')
                            ->default(function() use ($teacher) {
                                return $teacher ? $teacher->id : null;
                            }),

                        Forms\Components\Hidden::make('status')
                            ->default('active'),

                        Forms\Components\Hidden::make('max_score')
                            ->default(100),

                        Forms\Components\Toggle::make('notify_parents')
                            ->label('Send SMS notifications to parents')
                            ->default(true)
                            ->helperText('Automatically notify all parents of students in this grade'),
                    ])
                    ->columns(2),
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
                Tables\Columns\TextColumn::make('grade.name')
                    ->label('Grade')
                    ->sortable(),
                Tables\Columns\TextColumn::make('assignedBy.name')
                    ->sortable()
                    ->label('Assigned By'),
                Tables\Columns\TextColumn::make('due_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\IconColumn::make('homework_file')
                    ->label('File')
                    ->boolean()
                    ->getStateUsing(fn ($record) => !empty($record->homework_file)),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'active',
                        'success' => 'completed',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('subject')
                    ->relationship('subject', 'name'),
                Tables\Filters\SelectFilter::make('grade')
                    ->relationship('grade', 'name'),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'completed' => 'Completed',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('downloadFile')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->url(fn (Homework $record) => route('homework.download', $record))
                    ->openUrlInNewTab()
                    ->visible(fn (Homework $record) => !empty($record->homework_file)),
                Tables\Actions\Action::make('sendNotifications')
                    ->label('Send SMS')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('warning')
                    ->action(function (Homework $record) {
                        self::sendSmsNotifications($record);
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Send SMS Notifications')
                    ->modalDescription('This will send SMS notifications to all parents/guardians of students in this grade. Are you sure you want to continue?')
                    ->modalSubmitActionLabel('Yes, Send Notifications'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Send SMS notifications to parents about new homework
     */
    public static function sendSmsNotifications(Homework $homework): void
    {
        // Get all students in the specified grade using grade_id
        $students = Student::where('grade_id', $homework->grade_id)
            ->where('enrollment_status', 'active')
            ->with('parentGuardian')
            ->get();

        $successCount = 0;
        $failCount = 0;

        foreach ($students as $student) {
            $parentGuardian = $student->parentGuardian;

            if (!$parentGuardian || !$parentGuardian->phone) {
                $failCount++;
                continue;
            }

            try {
                // Construct the SMS message
                $subjectName = $homework->subject->name ?? 'Unknown Subject';
                $gradeName = $homework->grade->name ?? 'Unknown Grade';
                $dueDate = $homework->due_date->format('d/m/Y');

                $message = "Hello {$parentGuardian->name}, your child {$student->name} has new homework.\n\n";
                $message .= "Subject: {$subjectName}\n";
                $message .= "Title: {$homework->title}\n";
                $message .= "Grade: {$gradeName}\n";
                $message .= "Due Date: {$dueDate}\n\n";
                $message .= "Please check the parent portal to download the homework.";

                // Format and send SMS
                $formattedPhone = self::formatPhoneNumber($parentGuardian->phone);
                $success = self::sendMessage($message, $formattedPhone);

                if ($success) {
                    $successCount++;
                    // Log successful SMS
                    Log::info('Homework SMS sent', [
                        'homework_id' => $homework->id,
                        'student_id' => $student->id,
                        'parent_guardian_id' => $parentGuardian->id
                    ]);
                } else {
                    $failCount++;
                }

            } catch (\Exception $e) {
                $failCount++;
                // Log error
                Log::error('Failed to send homework SMS', [
                    'homework_id' => $homework->id,
                    'student_id' => $student->id,
                    'parent_guardian_id' => $parentGuardian->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Show notification with results
        Notification::make()
            ->title('SMS Notifications Sent')
            ->body("Successfully sent: {$successCount}, Failed: {$failCount}")
            ->success($successCount > 0)
            ->warning($failCount > 0)
            ->send();
    }

    public static function shouldRegisterNavigation(): bool
        {
            $user = Auth::user();

            if (!$user) {
                return false;
            }

            return $user->hasRole([ 'Student', 'Teacher']);
        }


    /**
     * Format phone number to ensure it has the country code
     */
    public static function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove any non-numeric characters
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

        // Check if number already has country code (260 for Zambia)
        if (substr($phoneNumber, 0, 3) === '260') {
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

    /**
     * Send a message via SMS
     */
    public static function sendMessage($message_string, $phone_number): bool
    {
        try {
            // Log the sending attempt
            Log::info('Sending homework SMS notification', [
                'phone' => $phone_number,
                'message' => substr($message_string, 0, 50) . '...'
            ]);

            // Replace @ with (at) for SMS compatibility
            $sms_message = str_replace('@', '(at)', $message_string);
            $url_encoded_message = urlencode($sms_message);

            $sendSenderSMS = Http::withoutVerifying()
                ->timeout(20)
                ->retry(3, 2000)
                ->post('https://www.cloudservicezm.com/smsservice/httpapi?username=Blessmore&password=Blessmore&msg=' . $url_encoded_message . '&shortcode=2343&sender_id=StFrancis&phone=' . $phone_number . '&api_key=121231313213123123');

            // Log the response
            Log::info('SMS API Response', [
                'status' => $sendSenderSMS->status(),
                'body' => $sendSenderSMS->body(),
                'to' => substr($phone_number, 0, 6) . '****' . substr($phone_number, -3),
            ]);

            return $sendSenderSMS->successful();
        } catch (\Exception $e) {
            Log::error('SMS sending failed', [
                'error' => $e->getMessage(),
                'phone' => $phone_number,
            ]);
            return false;
        }
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
