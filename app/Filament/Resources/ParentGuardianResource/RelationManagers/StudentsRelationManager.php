<?php

namespace App\Filament\Resources\ParentGuardianResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\User;
use App\Models\UserCredential;
use App\Models\Grade;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class StudentsRelationManager extends RelationManager
{
    protected static string $relationship = 'students';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('date_of_birth')
                    ->required(),
                Forms\Components\Select::make('gender')
                    ->options([
                        'male' => 'Male',
                        'female' => 'Female',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('student_id_number')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\Select::make('grade_id')
                    ->label('Grade')
                    ->relationship('grade', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\DatePicker::make('admission_date')
                    ->required(),
                Forms\Components\Select::make('enrollment_status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'graduated' => 'Graduated',
                        'transferred' => 'Transferred',
                    ])
                    ->default('active')
                    ->required(),
                Forms\Components\Textarea::make('address')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('student_id_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('grade.name')
                    ->label('Grade')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('gender'),
                Tables\Columns\TextColumn::make('date_of_birth')
                    ->date(),
                Tables\Columns\TextColumn::make('enrollment_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        'graduated' => 'info',
                        'transferred' => 'warning',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('enrollment_status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'graduated' => 'Graduated',
                        'transferred' => 'Transferred',
                    ]),
                Tables\Filters\SelectFilter::make('grade')
                    ->relationship('grade', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->after(function ($record, $data, $livewire) {
                        // Get the parent from the relationship
                        $parent = $livewire->getOwnerRecord();

                        // Send notification to parent about new student
                        $this->sendStudentCreationNotification($parent, $record, $data);

                        // Create user account for student if in qualifying grade
                        $this->createStudentAccountIfNeeded($parent, $record, $data);
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('sendSms')
                    ->label('Send SMS')
                    ->icon('heroicon-o-chat-bubble-left')
                    ->form([
                        Forms\Components\Textarea::make('message')
                            ->required()
                            ->default('Important message regarding your child {student_name}.')
                            ->helperText('You can use {parent_name}, {student_name}, and {grade} as placeholders.')
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data, $livewire) {
                        $parent = $livewire->getOwnerRecord();

                        if (!$parent->phone) {
                            Notification::make()
                                ->title('Cannot send SMS')
                                ->body('No parent/guardian phone number found.')
                                ->danger()
                                ->send();
                            return;
                        }

                        try {
                            // Get the grade name from the relationship
                            $gradeDisplay = $record->grade ? $record->grade->name : 'Unknown Grade';

                            // Personalize message
                            $personalizedMessage = str_replace(
                                ['{parent_name}', '{student_name}', '{grade}'],
                                [$parent->name, $record->name, $gradeDisplay],
                                $data['message']
                            );

                            // Send SMS
                            $formattedPhone = $this->formatPhoneNumber($parent->phone);
                            $this->sendMessage($personalizedMessage, $formattedPhone);

                            // Log success
                            Log::info('SMS sent to parent from relation manager', [
                                'student_id' => $record->id,
                                'parent_id' => $parent->id
                            ]);

                            // Notify admin
                            Notification::make()
                                ->title('SMS Sent')
                                ->body("Message sent to {$parent->name} successfully.")
                                ->success()
                                ->send();

                        } catch (\Exception $e) {
                            // Log error
                            Log::error('Failed to send SMS from relation manager', [
                                'student_id' => $record->id,
                                'parent_id' => $parent->id,
                                'error' => $e->getMessage()
                            ]);

                            // Notify admin
                            Notification::make()
                                ->title('SMS Failed')
                                ->body("Failed to send SMS: {$e->getMessage()}")
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('bulkSms')
                        ->label('Send Bulk SMS')
                        ->icon('heroicon-o-chat-bubble-left-ellipsis')
                        ->form([
                            Forms\Components\Textarea::make('message')
                                ->required()
                                ->default('Important message regarding your child(ren).')
                                ->rows(3),
                        ])
                        ->action(function ($records, array $data, $livewire) {
                            $parent = $livewire->getOwnerRecord();

                            if (!$parent->phone) {
                                Notification::make()
                                    ->title('Cannot send SMS')
                                    ->body('No parent/guardian phone number found.')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            try {
                                // Include student names in message
                                $studentNames = $records->pluck('name')->join(', ', ' and ');
                                $message = str_replace(
                                    ['{parent_name}', '{student_names}'],
                                    [$parent->name, $studentNames],
                                    $data['message']
                                );

                                // Send SMS
                                $formattedPhone = $this->formatPhoneNumber($parent->phone);
                                $this->sendMessage($message, $formattedPhone);

                                // Log success
                                Log::info('Bulk SMS sent to parent from relation manager', [
                                    'parent_id' => $parent->id,
                                    'students_count' => $records->count()
                                ]);

                                // Notify admin
                                Notification::make()
                                    ->title('Bulk SMS Sent')
                                    ->body("Message sent to {$parent->name} about {$records->count()} students.")
                                    ->success()
                                    ->send();

                            } catch (\Exception $e) {
                                // Log error
                                Log::error('Failed to send bulk SMS from relation manager', [
                                    'parent_id' => $parent->id,
                                    'students_count' => $records->count(),
                                    'error' => $e->getMessage()
                                ]);

                                // Notify admin
                                Notification::make()
                                    ->title('Bulk SMS Failed')
                                    ->body("Failed to send SMS: {$e->getMessage()}")
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    /**
     * Send notification to parent about newly added student
     */
    protected function sendStudentCreationNotification($parent, $student, $data)
    {
        try {
            // Get the parent's user account
            $user = User::where('email', $parent->email)
                        ->orWhere('id', $parent->user_id)
                        ->first();

            if (!$user || !$parent->phone) {
                Notification::make()
                    ->title('Information')
                    ->body("Student added but couldn't send SMS notification (no parent account or phone).")
                    ->warning()
                    ->send();
                return;
            }

            // Create message about the new student
            $message = "Hello {$parent->name}, your child {$data['name']} has been added to your parent portal account. You can now view their academic information by logging in with your username: {$user->username}";

            // Format the phone number
            $formattedPhone = $this->formatPhoneNumber($parent->phone);

            // Send the SMS
            $this->sendMessage($message, $formattedPhone);

            // Show success notification
            Notification::make()
                ->title('Student added to parent')
                ->body("Notification sent to {$parent->name} via SMS.")
                ->success()
                ->send();

        } catch (\Exception $e) {
            // Log the error
            Log::error('Failed to send SMS notification about new student', [
                'student_id' => $student->id,
                'student_name' => $data['name'],
                'error' => $e->getMessage()
            ]);

            // Show error notification
            Notification::make()
                ->title('Student added')
                ->body("Failed to send SMS notification to parent. Error: {$e->getMessage()}")
                ->warning()
                ->send();
        }
    }

    /**
     * Create a student account if they're in a qualifying grade
     */
    protected function createStudentAccountIfNeeded($parent, $student, $data)
    {
        // Get the grade information
        $grade = null;
        if (isset($data['grade_id'])) {
            $grade = Grade::find($data['grade_id']);
        }

        // Check if student should have their own account (grades 8-12)
        if (!$this->shouldHavePortalAccess($grade)) {
            return;
        }

        // Try to create an account for the student via DB transaction
        try {
            DB::transaction(function () use ($parent, $student, $data, $grade) {
                // Generate a secure random password
                $password = Str::password(10);

                // Create a user account for the student
                $user = User::create([
                    'name' => $data['name'],
                    'email' => $this->generateStudentEmail($data['name'], $data['student_id_number']),
                    'username' => $this->generateUsername($data['name'], $data['student_id_number']),
                    'password' => Hash::make($password),
                    'status' => 'active',
                ]);

                if (!$parent->phone) {
                    // Store credentials for manual retrieval if no phone
                    UserCredential::create([
                        'user_id' => $user->id,
                        'username' => $user->username,
                        'password' => $password,
                        'is_sent' => false,
                        'delivery_method' => 'manual',
                    ]);

                    throw new \Exception('No parent phone number found to send credentials');
                }

                // Send credentials to parent
                $message = "Hello {$parent->name}, your child {$data['name']}'s student portal account has been created. Username: {$user->username}, Password: {$password}. Please help them log in and change their password.";

                $formattedPhone = $this->formatPhoneNumber($parent->phone);
                $this->sendMessage($message, $formattedPhone);

                // Record successful SMS sending
                UserCredential::create([
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'password' => $password,
                    'is_sent' => true,
                    'sent_at' => now(),
                    'delivery_method' => 'sms',
                ]);

                // Log success
                Log::info('Student credentials sent to parent via SMS', [
                    'student_id' => $student->id,
                    'parent_id' => $parent->id,
                    'username' => $user->username
                ]);
            });

            // Show success notification
            Notification::make()
                ->title('Student portal account created')
                ->body("Student account created and credentials sent to parent.")
                ->success()
                ->send();

        } catch (\Exception $e) {
            // Log the error
            Log::error('Failed to create student account or send credentials', [
                'student_id' => $student->id,
                'error' => $e->getMessage()
            ]);

            // Show error notification
            Notification::make()
                ->title('Student account issue')
                ->body("Student was added, but there was an issue with account creation: {$e->getMessage()}")
                ->warning()
                ->send();
        }
    }

    /**
     * Determine if a student should have portal access based on their grade
     */
    protected function shouldHavePortalAccess($grade): bool
    {
        if (!$grade) {
            return false;
        }

        // Extract grade level from the grade name or use the level field
        $gradeLevel = $grade->level ?? 0;

        // If level is not set, try to extract from name
        if ($gradeLevel == 0 && $grade->name) {
            $normalizedGrade = preg_replace('/[^0-9]/', '', $grade->name);
            $gradeLevel = (int) $normalizedGrade;
        }

        // Secondary school students (typically grades 8-12) get portal access
        return $gradeLevel >= 8 && $gradeLevel <= 12;
    }

    /**
     * Generate a username from the student's name and ID
     */
    protected function generateUsername(string $name, string $studentId): string
    {
        // Use first letter of first name + last name + last 4 digits of ID
        $nameParts = explode(' ', $name);
        $firstName = $nameParts[0] ?? '';
        $lastName = end($nameParts) ?? '';

        $idSuffix = substr($studentId, -4);

        $baseUsername = strtolower(substr($firstName, 0, 1) . $lastName . $idSuffix);
        $baseUsername = preg_replace('/[^a-z0-9]/', '', $baseUsername); // Remove special characters

        // Check if the username exists, if it does, append numbers
        $username = $baseUsername;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        return $username;
    }

    /**
     * Generate a school email for the student
     */
    protected function generateStudentEmail(string $name, string $studentId): string
    {
        // Create an email like firstname.lastname@stfrancisofassisi.tech
        $nameParts = explode(' ', $name);
        $firstName = $nameParts[0] ?? '';
        $lastName = end($nameParts) ?? '';

        $baseEmail = strtolower($firstName . '.' . $lastName);
        $baseEmail = preg_replace('/[^a-z0-9\.]/', '', $baseEmail); // Remove special characters

        // Check if the email exists, if it does, append numbers
        $email = $baseEmail . '@stfrancisofassisi.tech';
        $counter = 1;

        while (User::where('email', $email)->exists()) {
            $email = $baseEmail . $counter . '@stfrancisofassisi.tech';
            $counter++;
        }

        return $email;
    }

    /**
     * Format phone number to ensure it has the country code
     */
    protected function formatPhoneNumber(string $phoneNumber): string
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
     * Send a message via SMS
     */
    protected function sendMessage($message_string, $phone_number)
    {
        try {
            // Log the sending attempt
            Log::info('Sending SMS notification', [
                'phone' => $phone_number,
                'message' => substr($message_string, 0, 30) . '...' // Only log beginning of message for privacy
            ]);

            $url_encoded_message = urlencode($message_string);

            $sendSenderSMS = Http::withoutVerifying()
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
            throw $e; // Re-throw to be caught by the calling method
        }
    }
}
