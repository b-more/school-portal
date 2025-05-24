<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentResource\Pages;
use App\Filament\Resources\StudentResource\RelationManagers;
use App\Models\Student;
use App\Models\User;
use App\Models\ParentGuardian;
use App\Models\UserCredential;
use App\Models\Grade;
use App\Models\Teacher;
use App\Models\SmsLog;
use App\Constants\RoleConstants;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Student Management';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withCount('results')
            ->withCount('fees');

        $user = Auth::user();

        // Admin can see all students
        if ($user->role_id === RoleConstants::ADMIN) {
            return $query;
        }

        // Teachers can only see students in their classes
        if ($user->role_id === RoleConstants::TEACHER) {
            $teacher = Teacher::where('user_id', $user->id)->first();

            if ($teacher) {
                // Get students from classes where this teacher teaches
                $classSectionIds = $teacher->classSections()->pluck('id')->toArray();
                return $query->whereIn('class_section_id', $classSectionIds);
            }

            return $query->where('id', 0); // Return empty if teacher not found
        }

        // Parents can only see their own children
        if ($user->role_id === RoleConstants::PARENT) {
            $parent = $user->parentGuardian;

            if ($parent) {
                return $query->where('parent_guardian_id', $parent->id);
            }

            return $query->where('id', 0); // Return empty if parent not found
        }

        // All other roles have no access
        return $query->where('id', 0);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Personal Information')
                            ->description('Enter the student\'s basic personal details')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Full Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Enter full name as it appears on official documents'),

                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\DatePicker::make('date_of_birth')
                                            ->label('Date of Birth')
                                            ->required()
                                            ->displayFormat('d/m/Y')
                                            ->closeOnDateSelection()
                                            ->weekStartsOnSunday(),

                                        Forms\Components\TextInput::make('place_of_birth')
                                            ->label('Place of Birth')
                                            ->maxLength(255)
                                            ->placeholder('e.g., Lusaka, Zambia'),
                                    ])
                                    ->columns(2),

                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\Select::make('gender')
                                            ->options([
                                                'male' => 'Male',
                                                'female' => 'Female',
                                            ])
                                            ->required(),

                                        Forms\Components\Select::make('religious_denomination')
                                            ->options([
                                                'Christian' => 'Christian',
                                                'Catholic' => 'Catholic',
                                                'Protestant' => 'Protestant',
                                                'Pentecostal' => 'Pentecostal',
                                                'SDA' => 'Seventh Day Adventist',
                                                'Anglican' => 'Anglican',
                                                'Muslim' => 'Muslim',
                                                'Hindu' => 'Hindu',
                                                'Buddhist' => 'Buddhist',
                                                'Traditional' => 'Traditional',
                                                'Other' => 'Other',
                                                'None' => 'None',
                                            ])
                                            ->searchable()
                                            ->placeholder('Select denomination'),
                                    ])
                                    ->columns(2),

                                Forms\Components\Textarea::make('address')
                                    ->label('Residential Address')
                                    ->maxLength(255)
                                    ->placeholder('Enter full residential address')
                                    ->rows(2),
                            ]),

                        Forms\Components\Section::make('Education Details')
                            ->description('Information about student\'s current and previous education')
                            ->icon('heroicon-o-academic-cap')
                            ->schema([
                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\Card::make()
                                            ->schema([
                                                Forms\Components\Select::make('grade_id')
                                                    ->label('Grade')
                                                    ->options(function () {
                                                        return Grade::query()
                                                            ->where('is_active', true)
                                                            ->orderBy('level')
                                                            ->get()
                                                            ->pluck('name', 'id');
                                                    })
                                                    ->required()
                                                    ->searchable()
                                                    ->preload()
                                                    ->live()
                                                    ->afterStateUpdated(function ($state, callable $set) {
                                                        // Clear dependent selections when this changes
                                                        $set('fee_structure_id', null);
                                                        $set('student_id', null);
                                                        $set('balance', null);
                                                    }),

                                                Forms\Components\Hidden::make('student_id_number')
                                                    ->dehydrated(true)
                                                    ->default(null),

                                                Forms\Components\Placeholder::make('student_id_preview')
                                                    ->label('Student ID')
                                                    ->content(function (callable $get, ?string $state) {
                                                        $gradeId = $get('grade_id');
                                                        if (!$gradeId) {
                                                            return 'Select a grade to generate Student ID';
                                                        }

                                                        // Get the grade model to access the name
                                                        $grade = Grade::find($gradeId);
                                                        if (!$grade) {
                                                            return 'Invalid grade selected';
                                                        }

                                                        // Generate the ID if not already set
                                                        return self::generateStudentId($grade->name);
                                                    }),
                                            ]),

                                        Forms\Components\Card::make()
                                            ->schema([
                                                Forms\Components\Select::make('standard_of_education')
                                                    ->label('Level of Education')
                                                    ->options([
                                                        'Nursery' => 'Nursery',
                                                        'Primary' => 'Primary',
                                                        'Junior Secondary' => 'Junior Secondary',
                                                        'Senior Secondary' => 'Senior Secondary',
                                                    ])
                                                    ->helperText('Educational category the student belongs to'),

                                                Forms\Components\Select::make('enrollment_status')
                                                    ->options([
                                                        'active' => 'Active',
                                                        'inactive' => 'Inactive',
                                                        'graduated' => 'Graduated',
                                                        'transferred' => 'Transferred',
                                                    ])
                                                    ->default('active')
                                                    ->required(),
                                            ]),
                                    ])
                                    ->columns(2),

                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\DatePicker::make('admission_date')
                                            ->label('Date of Admission')
                                            ->required()
                                            ->default(now())
                                            ->displayFormat('d/m/Y'),

                                        Forms\Components\TextInput::make('previous_school')
                                            ->label('Previous School')
                                            ->maxLength(255)
                                            ->placeholder('Name of previous institution (if any)'),
                                    ])
                                    ->columns(2),
                            ]),

                        Forms\Components\Section::make('Medical Information')
                            ->description('Health-related information for school records')
                            ->icon('heroicon-o-heart')
                            ->schema([
                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\Select::make('smallpox_vaccination')
                                            ->label('Smallpox Vaccination')
                                            ->options([
                                                'Yes' => 'Yes',
                                                'No' => 'No',
                                                'Not Sure' => 'Not Sure',
                                            ])
                                            ->required()
                                            ->live(),

                                        Forms\Components\DatePicker::make('date_vaccinated')
                                            ->label('Date of Vaccination')
                                            ->displayFormat('d/m/Y')
                                            ->visible(fn (callable $get) => $get('smallpox_vaccination') === 'Yes'),
                                    ])
                                    ->columns(2),

                                Forms\Components\Textarea::make('medical_information')
                                    ->label('Other Medical Information')
                                    ->maxLength(65535)
                                    ->rows(3)
                                    ->placeholder('Include any allergies, medical conditions, or medications')
                                    ->helperText('Please include important health information the school should be aware of'),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Profile Image')
                            ->schema([
                                Forms\Components\FileUpload::make('profile_photo')
                                    ->label('Student Photo')
                                    ->image()
                                    ->directory('student-photos')
                                    ->maxSize(2048)
                                    ->imageCropAspectRatio('1:1')
                                    ->imageResizeTargetWidth('300')
                                    ->imageResizeTargetHeight('300'),
                            ]),

                        Forms\Components\Section::make('Parent/Guardian Information')
                            ->description('Student\'s parent or guardian details')
                            ->icon('heroicon-o-users')
                            ->schema([
                                Forms\Components\Select::make('parent_guardian_id')
                                    ->relationship('parentGuardian', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Full Name')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('email')
                                            ->label('Email Address')
                                            ->email()
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('phone')
                                            ->label('Phone Number')
                                            ->required()
                                            ->tel()
                                            ->placeholder('+260 XXX XXX XXX')
                                            ->maxLength(255),
                                        Forms\Components\Select::make('relationship')
                                            ->label('Relationship to Student')
                                            ->options([
                                                'father' => 'Father',
                                                'mother' => 'Mother',
                                                'guardian' => 'Guardian',
                                                'other' => 'Other',
                                            ])
                                            ->required(),
                                        Forms\Components\TextInput::make('address')
                                            ->label('Contact Address')
                                            ->required(),
                                    ])
                                    ->required(),
                            ]),

                        Forms\Components\Section::make('Additional Notes')
                            ->schema([
                                Forms\Components\Textarea::make('notes')
                                    ->maxLength(65535)
                                    ->rows(5)
                                    ->placeholder('Any additional information about the student')
                                    ->helperText('For internal use only'),
                            ])
                            ->collapsible(),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        $user = Auth::user();
        $isAdmin = $user->role_id === RoleConstants::ADMIN;
        $isTeacher = $user->role_id === RoleConstants::TEACHER;
        $isParent = $user->role_id === RoleConstants::PARENT;

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student_id_number')
                    ->label('Student ID')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('grade.name')
                    ->label('Grade')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('parentGuardian.name')
                    ->sortable()
                    ->label('Parent/Guardian')
                    ->visible($isTeacher || $isAdmin), // Hide from parents
                Tables\Columns\TextColumn::make('gender')
                    ->searchable(),
                Tables\Columns\TextColumn::make('date_of_birth')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('enrollment_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        'graduated' => 'info',
                        'transferred' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('admission_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('enrollment_status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'graduated' => 'Graduated',
                        'transferred' => 'Transferred',
                    ]),
                Tables\Filters\SelectFilter::make('grade_id')
                    ->label('Grade')
                    ->relationship('grade', 'name')
                    ->visible($isTeacher || $isAdmin), // Parents don't need this filter
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible($isAdmin), // Only admin can edit
                Tables\Actions\Action::make('sendNotification')
                    ->label('Send SMS')
                    ->icon('heroicon-o-chat-bubble-left')
                    ->form([
                        Forms\Components\Textarea::make('message')
                            ->required()
                            ->default('Dear parent, this is an important message regarding your child.')
                            ->placeholder('Enter the message to send to parent/guardian')
                            ->rows(3),
                    ])
                    ->action(function (Student $record, array $data) {
                        // Get parent guardian
                        $parentGuardian = ParentGuardian::find($record->parent_guardian_id);

                        if (!$parentGuardian || !$parentGuardian->phone) {
                            Notification::make()
                                ->title('Cannot send SMS')
                                ->body('No parent/guardian phone number found.')
                                ->danger()
                                ->send();
                            return;
                        }

                        try {
                            // Personalize message
                            $message = str_replace(
                                ['{parent_name}', '{student_name}', '{grade}'],
                                [$parentGuardian->name, $record->name, $record->grade?->name ?? 'Unknown'],
                                $data['message']
                            );

                            // Format phone and send message
                            $formattedPhone = self::formatPhoneNumber($parentGuardian->phone);

                            // Send the SMS with specific message type and reference
                            $sent = self::sendMessage(
                                $message,
                                $formattedPhone,
                                'student_notification',
                                $record->id
                            );

                            // Show success notification
                            Notification::make()
                                ->title('SMS Sent')
                                ->body("Message sent to {$parentGuardian->name} successfully.")
                                ->success()
                                ->send();

                        } catch (\Exception $e) {
                            // Log the error
                            Log::error('Failed to send notification via SMS', [
                                'student_id' => $record->id,
                                'parent_guardian_id' => $parentGuardian->id,
                                'error' => $e->getMessage()
                            ]);

                            // Notify the admin of the SMS failure
                            Notification::make()
                                ->title('SMS Failed')
                                ->body("Failed to send SMS to {$parentGuardian->name}: {$e->getMessage()}")
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible($isAdmin || $isTeacher), // Only admin and teachers can send SMS
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible($isAdmin),
                    Tables\Actions\BulkAction::make('updateStatus')
                        ->label('Update Status')
                        ->icon('heroicon-o-pencil-square')
                        ->form([
                            Forms\Components\Select::make('enrollment_status')
                                ->options([
                                    'active' => 'Active',
                                    'inactive' => 'Inactive',
                                    'graduated' => 'Graduated',
                                    'transferred' => 'Transferred',
                                ])
                                ->required(),
                        ])
                        ->action(function (Builder $query, array $data): void {
                            $query->update(['enrollment_status' => $data['enrollment_status']]);
                        })
                        ->deselectRecordsAfterCompletion()
                        ->visible($isAdmin), // Only admin can update status
                    Tables\Actions\BulkAction::make('bulkSms')
    ->label('Send Bulk SMS')
    ->icon('heroicon-o-chat-bubble-left-ellipsis')
    ->form([
        Forms\Components\Textarea::make('message')
            ->label('SMS Message')
            ->required()
            ->default('Dear {parent_name}, this is an important message about your child {student_name} in {grade}.')
            ->helperText('You can use placeholders: {parent_name}, {student_name}, and {grade}')
            ->placeholder('Enter message to send to parents/guardians')
            ->rows(3),
    ])
    ->action(function ($records, array $data): void {
        // Instead of using Builder query, work directly with the selected records
        // $records is a Collection of Student models

        $successCount = 0;
        $failedCount = 0;

        foreach ($records as $student) {
            // Ensure we have the necessary relationships loaded
            if (!$student->relationLoaded('parentGuardian')) {
                $student->load('parentGuardian');
            }
            if (!$student->relationLoaded('grade')) {
                $student->load('grade');
            }

            // Check if parent guardian exists and has phone
            if (!$student->parentGuardian || !$student->parentGuardian->phone) {
                $failedCount++;
                continue;
            }

            try {
                // Personalize message with parent and student names
                $personalizedMessage = str_replace(
                    ['{parent_name}', '{student_name}', '{grade}'],
                    [
                        $student->parentGuardian->name,
                        $student->name,
                        $student->grade?->name ?? 'Unknown'
                    ],
                    $data['message']
                );

                // Format phone and send
                $formattedPhone = self::formatPhoneNumber($student->parentGuardian->phone);

                // Send the SMS with specific message type and reference
                $sent = self::sendMessage(
                    $personalizedMessage,
                    $formattedPhone,
                    'student_bulk_notification',
                    $student->id
                );

                if ($sent) {
                    $successCount++;
                } else {
                    $failedCount++;
                }
            } catch (\Exception $e) {
                $failedCount++;

                // Log error
                Log::error('Failed to send bulk SMS', [
                    'student_id' => $student->id,
                    'parent_guardian_id' => $student->parentGuardian?->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Show notification with results
        Notification::make()
            ->title('Bulk SMS Results')
            ->body("Successfully sent: {$successCount}, Failed: {$failedCount}")
            ->success($successCount > 0)
            ->warning($failedCount > 0)
            ->send();
    })
    ->deselectRecordsAfterCompletion()
    ->visible($isAdmin || $isTeacher), // Only admin and teachers can send bulk SMS
]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Add your relation managers here
            // RelationManagers\ResultsRelationManager::class,
            // RelationManagers\FeesRelationManager::class,
            // RelationManagers\HomeworkSubmissionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            //'view' => Pages\ViewStudent::route('/{record}'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }

    /**
     * Generate a student ID based on the student's grade
     * Format: SFG1001, SFG2001, etc.
     */
    public static function generateStudentId(string $grade): string
    {
        // Map different grades to their prefix codes
        $gradeMap = [
            'Baby Class' => 'SFBC',
            'Middle Class' => 'SFMC',
            'Reception' => 'SFR',
            'Grade 1' => 'SFG1',
            'Grade 2' => 'SFG2',
            'Grade 3' => 'SFG3',
            'Grade 4' => 'SFG4',
            'Grade 5' => 'SFG5',
            'Grade 6' => 'SFG6',
            'Grade 7' => 'SFG7',
            'Grade 8' => 'SFG8',
            'Grade 9' => 'SFG9',
            'Grade 10' => 'SFG10',
            'Grade 11' => 'SFG11',
            'Grade 12' => 'SFG12',
        ];

        $prefix = $gradeMap[$grade] ?? 'SF';

        // Get the latest student number for this grade
        $lastStudent = Student::whereHas('grade', function($query) use ($grade) {
                $query->where('name', $grade);
            })
            ->where('student_id_number', 'like', $prefix . '%')
            ->orderBy('student_id_number', 'desc')
            ->first();

        if ($lastStudent) {
            // Extract the numeric part from the last ID
            $lastIdNumber = (int) substr($lastStudent->student_id_number, strlen($prefix));
            $newIdNumber = $lastIdNumber + 1;
        } else {
            $newIdNumber = 1;
        }

        // Format with leading zeros to ensure 3 digits
        return $prefix . str_pad($newIdNumber, 3, '0', STR_PAD_LEFT);
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

    /**
     * Calculate the cost of an SMS based on message length
     */
    private static function calculateMessageCost(string $message): float
    {
        $length = strlen($message);

        // Standard GSM 03.38 character set: 160 chars per SMS
        // Unicode messages: 70 chars per SMS
        $hasUnicode = preg_match('/[^\x20-\x7E]/', $message);

        $parts = $hasUnicode ? ceil($length / 70) : ceil($length / 160);
        return 0.50 * $parts; // 0.50 per message part
    }

    /**
     * Send a message via SMS and log it to the SMS log
     *
     * @param string $message_string The message content
     * @param string $phone_number The recipient's phone number
     * @param string $message_type The type of message (general, student_notification, etc.)
     * @param int|null $reference_id The ID of the related record (e.g., student ID)
     * @return bool Whether the message was sent successfully
     */
    // public static function sendMessage($message_string, $phone_number, $message_type = 'general', $reference_id = null)
    // {
    //     try {
    //         // Log the sending attempt
    //         Log::info('Sending SMS notification', [
    //             'phone' => $phone_number,
    //             'message' => substr($message_string, 0, 30) . '...' // Only log beginning of message for privacy
    //         ]);

    //         // Create an SMS log entry before sending
    //         $smsLog = SmsLog::create([
    //             'recipient' => $phone_number,
    //             'message' => $message_string,
    //             'status' => 'pending',
    //             'message_type' => $message_type,
    //             'reference_id' => $reference_id,
    //             'cost' => self::calculateMessageCost($message_string),
    //             'sent_by' => Auth::id(),
    //         ]);

    //         // Send the SMS
    //         $url_encoded_message = urlencode($message_string);
    //         $sendSenderSMS = Http::withoutVerifying()
    //             ->post('https://www.cloudservicezm.com/smsservice/httpapi?username=Blessmore&password=Blessmore&msg=' . $url_encoded_message . '&shortcode=2343&sender_id=StFrancis&phone=' . $phone_number . '&api_key=121231313213123123');

    //         // Update the SMS log with the result
    //         $smsLog->update([
    //             'status' => $sendSenderSMS->successful() ? 'sent' : 'failed',
    //             'provider_reference' => $sendSenderSMS->json('message_id') ?? null,
    //             'error_message' => $sendSenderSMS->successful() ? null : $sendSenderSMS->body(),
    //         ]);

    //         // Log the response
    //         Log::info('SMS API Response', [
    //             'status' => $sendSenderSMS->status(),
    //             'body' => $sendSenderSMS->body(),
    //             'to' => substr($phone_number, 0, 6) . '****' . substr($phone_number, -3),
    //             'sms_log_id' => $smsLog->id
    //         ]);

    //         return $sendSenderSMS->successful();
    //     } catch (\Exception $e) {
    //         // Update the SMS log with the error if it exists
    //         if (isset($smsLog)) {
    //             $smsLog->update([
    //                 'status' => 'failed',
    //                 'error_message' => $e->getMessage(),
    //             ]);
    //         }

    //         Log::error('SMS sending failed', [
    //             'error' => $e->getMessage(),
    //             'phone' => $phone_number,
    //             'sms_log_id' => $smsLog->id ?? null
    //         ]);
    //         throw $e; // Re-throw to be caught by the calling method
    //     }
    // }

    /**
 * Send a message via SMS and log it to the SMS log
 *
 * @param string $message_string The message content
 * @param string $phone_number The recipient's phone number
 * @param string $message_type The type of message (general, student_notification, etc.)
 * @param int|null $reference_id The ID of the related record (e.g., student ID)
 * @return bool Whether the message was sent successfully
 */
    /**
 * Send a message via SMS and log it
 *
 * @param string $message_string The message content
 * @param string $phone_number The recipient's phone number
 * @param string $message_type The type of message (general, student_notification, etc.)
 * @param int|null $reference_id The ID of the related record (e.g., student ID)
 * @return bool Whether the message was sent successfully
 */
public static function sendMessage($message_string, $phone_number, $message_type = 'general', $reference_id = null)
{
    try {
        // Send the SMS
        $url_encoded_message = urlencode($message_string);
        $sendSenderSMS = Http::withoutVerifying()
            ->post('https://www.cloudservicezm.com/smsservice/httpapi?username=Blessmore&password=Blessmore&msg=' . $url_encoded_message . '&shortcode=2343&sender_id=StFrancis&phone=' . $phone_number . '&api_key=121231313213123123');

        // Map custom message types to allowed enum values
        $allowedMessageTypes = [
            'homework_notification',
            'result_notification',
            'fee_reminder',
            'event_notification',
            'general',
            'other'
        ];

        // Map your custom types to valid enum values
        $mappedMessageType = 'general'; // Default

        if ($message_type === 'student_notification' || $message_type === 'student_bulk_notification') {
            $mappedMessageType = 'general';
        } else if (in_array($message_type, $allowedMessageTypes)) {
            $mappedMessageType = $message_type;
        }

        // Calculate message cost
        $messageParts = ceil(strlen($message_string) / 160);
        $cost = 0.50 * $messageParts;

        // Create log entry
        DB::table('sms_logs')->insert([
            'recipient' => $phone_number,
            'message' => $message_string,
            'status' => $sendSenderSMS->successful() ? 'sent' : 'failed',
            'message_type' => $mappedMessageType,
            'reference_id' => $reference_id,
            'cost' => $cost,
            'provider_reference' => $sendSenderSMS->json('message_id') ?? null,
            'error_message' => $sendSenderSMS->successful() ? null : $sendSenderSMS->body(),
            'sent_by' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Log the response
        Log::info('SMS API Response', [
            'status' => $sendSenderSMS->status(),
            'body' => $sendSenderSMS->body(),
            'to' => substr($phone_number, 0, 6) . '****' . substr($phone_number, -3),
            'successful' => $sendSenderSMS->successful()
        ]);

        return $sendSenderSMS->successful();
    } catch (\Exception $e) {
        // Log the error
        Log::error('SMS sending failed', [
            'error' => $e->getMessage(),
            'phone' => $phone_number
        ]);

        // Try to log the failure
        try {
            DB::table('sms_logs')->insert([
                'recipient' => $phone_number,
                'message' => $message_string,
                'status' => 'failed',
                'message_type' => 'general', // Safe fallback
                'reference_id' => $reference_id,
                'cost' => ceil(strlen($message_string) / 160) * 0.50,
                'error_message' => $e->getMessage(),
                'sent_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } catch (\Exception $logException) {
            Log::critical('Could not log SMS failure: ' . $logException->getMessage());
        }

        throw $e; // Re-throw to be caught by the calling method
    }
}

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        // Admin, Teachers, Nurses, and Parents can access
        if (in_array($user->role_id, [
            RoleConstants::ADMIN,
            RoleConstants::TEACHER,
            RoleConstants::NURSE,
            RoleConstants::PARENT
        ])) {
            return true;
        }

        return false;
    }

    public static function canCreate(): bool
    {
        // Only admin can create students
        return Auth::user()?->role_id === RoleConstants::ADMIN ?? false;
    }

    public static function canEditAny(): bool
    {
        // Only admin can edit students
        return Auth::user()?->role_id === RoleConstants::ADMIN ?? false;
    }

    public static function canDeleteAny(): bool
    {
        // Only admin can delete students
        return Auth::user()?->role_id === RoleConstants::ADMIN ?? false;
    }
}
