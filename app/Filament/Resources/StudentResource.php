<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentResource\Pages;
use App\Filament\Resources\StudentResource\RelationManagers;
use App\Models\Student;
use App\Models\User;
use App\Models\ParentGuardian;
use App\Models\UserCredential;
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

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Student Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Personal Information')
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
                                'other' => 'Other',
                            ])
                            ->required(),
                        Forms\Components\FileUpload::make('profile_photo')
                            ->image()
                            ->directory('student-photos')
                            ->maxSize(2048)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('School Information')
                    ->schema([
                        Forms\Components\TextInput::make('student_id_number')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
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
                        Forms\Components\TextInput::make('previous_school')
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Parent/Guardian Information')
                    ->schema([
                        Forms\Components\Select::make('parent_guardian_id')
                            ->relationship('parentGuardian', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('phone')
                                    ->required()
                                    ->tel()
                                    ->maxLength(255),
                                Forms\Components\Select::make('relationship')
                                    ->options([
                                        'father' => 'Father',
                                        'mother' => 'Mother',
                                        'guardian' => 'Guardian',
                                        'other' => 'Other',
                                    ])
                                    ->required(),
                                Forms\Components\TextInput::make('address')
                                    ->required(),
                            ])
                            ->required(),
                    ]),

                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Textarea::make('address')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('medical_information')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('notes')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student_id_number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('grade')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('parentGuardian.name')
                    ->sortable()
                    ->label('Parent/Guardian'),
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
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('sendNotification')
                    ->label('Send SMS')
                    ->icon('heroicon-o-chat-bubble-left')
                    ->form([
                        Forms\Components\TextArea::make('message')
                            ->required()
                            ->default('Important message regarding your child')
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
                                [$parentGuardian->name, $record->name, $record->grade],
                                $data['message']
                            );

                            // Format phone and send message
                            $formattedPhone = self::formatPhoneNumber($parentGuardian->phone);
                            self::sendMessage($message, $formattedPhone);

                            // Log the successful SMS
                            Log::info('Notification sent to parent via SMS', [
                                'student_id' => $record->id,
                                'parent_guardian_id' => $parentGuardian->id,
                                'phone' => substr($formattedPhone, 0, 6) . '****' . substr($formattedPhone, -3)
                            ]);

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
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
                        ->deselectRecordsAfterCompletion(),
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
                        ->action(function (Builder $query, array $data): void {
                            // Get all students and their parent/guardian info
                            $students = $query->with('parentGuardian')->get();

                            $successCount = 0;
                            $failedCount = 0;

                            foreach ($students as $student) {
                                if (!$student->parentGuardian || !$student->parentGuardian->phone) {
                                    $failedCount++;
                                    continue;
                                }

                                try {
                                    // Personalize message with parent and student names
                                    $personalizedMessage = str_replace(
                                        ['{parent_name}', '{student_name}', '{grade}'],
                                        [$student->parentGuardian->name, $student->name, $student->grade],
                                        $data['message']
                                    );

                                    // Format phone and send
                                    $formattedPhone = self::formatPhoneNumber($student->parentGuardian->phone);
                                    self::sendMessage($personalizedMessage, $formattedPhone);

                                    $successCount++;

                                    // Log success
                                    Log::info('Bulk SMS sent', [
                                        'student_id' => $student->id,
                                        'parent_guardian_id' => $student->parentGuardian->id
                                    ]);
                                } catch (\Exception $e) {
                                    $failedCount++;

                                    // Log error
                                    Log::error('Failed to send bulk SMS', [
                                        'student_id' => $student->id,
                                        'parent_guardian_id' => $student->parentGuardian->id,
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
                        ->deselectRecordsAfterCompletion(),
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount('results')
            ->withCount('fees');
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
     * Send a message via SMS
     */
    public static function sendMessage($message_string, $phone_number)
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
