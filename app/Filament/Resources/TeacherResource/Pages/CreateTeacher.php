<?php

namespace App\Filament\Resources\TeacherResource\Pages;

use App\Constants\RoleConstants;
use App\Filament\Resources\TeacherResource;
use App\Mail\StaffCredentialsCreated;
use App\Models\AcademicYear;
use App\Models\ClassSection;
use App\Models\User;
use App\Models\UserCredential;
use App\Services\SmsService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CreateTeacher extends CreateRecord
{
    protected static string $resource = TeacherResource::class;

    /**
     * Prepare data before saving to database
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Handle the different teacher types
        if (isset($data['teacher_type'])) {
            if ($data['teacher_type'] === 'primary') {
                // For primary teachers - ensure all required fields are set
                $data['is_grade_teacher'] = true;
                $data['is_class_teacher'] = true;
                $data['specialization'] = null; // Primary teachers don't have specialization

                // Ensure grade_id and class_section_id are properly set
                if (! isset($data['grade_id']) || ! $data['grade_id']) {
                    throw new \Exception('Grade is required for primary teachers');
                }

                if (! isset($data['class_section_id']) || ! $data['class_section_id']) {
                    throw new \Exception('Class section is required for primary teachers');
                }
            } elseif ($data['teacher_type'] === 'secondary') {
                // For secondary teachers
                $data['is_class_teacher'] = false;
                $data['class_section_id'] = null; // Secondary teachers aren't assigned to specific class sections initially

                // Only set grade_id if they are a grade teacher
                if (! ($data['is_grade_teacher'] ?? false)) {
                    $data['grade_id'] = null;
                }

                // Ensure specialization is set
                if (! isset($data['specialization']) || ! $data['specialization']) {
                    throw new \Exception('Specialization is required for secondary teachers');
                }
            }
        }

        // Remove form-specific fields before saving
        unset($data['teacher_type']);
        unset($data['subject_classes']);
        unset($data['auto_assigned_subjects']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $teacher = $this->record;
        $data = $this->form->getRawState();

        // Create user account automatically
        $this->createUserAccount($teacher, $data);

        // Handle primary teachers - assign all grade subjects
        if ($teacher->isPrimaryTeacher() && $teacher->grade_id && $teacher->class_section_id) {
            $this->assignAllSubjectsToGrade($teacher);
        }

        // Handle secondary teachers - assign specific subjects to specific classes
        if (isset($data['teacher_type']) && $data['teacher_type'] === 'secondary' && isset($data['subject_classes'])) {
            $this->handleSecondaryTeacher($teacher, $data);
        }
    }

    /**
     * Automatically create user account for teacher
     */
    private function createUserAccount($teacher, $data): void
    {
        try {
            // Generate email if not provided
            $email = $teacher->email;
            if (empty($email)) {
                $email = $this->generateEmail($teacher->name, $teacher->employee_id);
                $teacher->update(['email' => $email]);
            }

            // Check if user already exists
            if ($teacher->user_id) {
                return;
            }

            // Generate secure password
            $password = Str::password(12);

            // Create user account
            $user = User::create([
                'name' => $teacher->name,
                'email' => $email,
                'password' => Hash::make($password),
                'role_id' => RoleConstants::TEACHER,
                'status' => 'active',
            ]);

            // Link teacher to user
            $teacher->update(['user_id' => $user->id]);

            // Store credentials
            UserCredential::create([
                'user_id' => $user->id,
                'username' => $email,
                'password' => $password,
                'is_sent' => false,
                'delivery_method' => 'email_and_sms',
            ]);

            // Track notification results
            $emailSent = false;
            $smsSent = false;

            // Send credentials via email
            $emailSent = $this->sendCredentialsEmail($teacher->name, $email, $password, 'Teacher');

            // Send credentials via SMS if phone number is available
            if (! empty($teacher->phone)) {
                $smsSent = $this->sendCredentialsSms($teacher->name, $email, $password, $teacher->phone, $user->id);
            }

            // Update credential record based on what was sent
            if ($emailSent || $smsSent) {
                UserCredential::where('username', $email)->update([
                    'is_sent' => true,
                    'sent_at' => now(),
                    'delivery_method' => $emailSent && $smsSent ? 'email_and_sms' : ($emailSent ? 'email' : 'sms'),
                ]);
            }

            // Build notification message
            $notificationBody = "Email: {$email} | Password: {$password}\n";
            if ($emailSent && $smsSent) {
                $notificationBody = 'Login credentials sent via EMAIL and SMS. '.$notificationBody;
            } elseif ($emailSent) {
                $notificationBody = 'Login credentials sent via EMAIL. '.$notificationBody;
            } elseif ($smsSent) {
                $notificationBody = 'Login credentials sent via SMS. '.$notificationBody;
            } else {
                $notificationBody = 'Credentials created but delivery failed. '.$notificationBody;
            }

            // Show success notification with credentials
            Notification::make()
                ->title('Teacher Account Created Successfully')
                ->body($notificationBody)
                ->success()
                ->persistent()
                ->send();

            Log::info('Teacher account created', [
                'teacher_id' => $teacher->id,
                'email' => $email,
                'user_id' => $user->id,
                'email_sent' => $emailSent,
                'sms_sent' => $smsSent,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create teacher account', [
                'teacher_id' => $teacher->id,
                'error' => $e->getMessage(),
            ]);

            Notification::make()
                ->title('Account Creation Warning')
                ->body('Teacher created but user account creation failed. Please create manually.')
                ->warning()
                ->send();
        }
    }

    /**
     * Generate email from name and employee ID
     */
    private function generateEmail(string $name, string $employeeId): string
    {
        $nameParts = explode(' ', $name);
        $firstName = strtolower($nameParts[0] ?? '');
        $lastName = strtolower(end($nameParts) ?? '');

        $baseEmail = $firstName.'.'.$lastName;
        $baseEmail = preg_replace('/[^a-z0-9\.]/', '', $baseEmail);

        $email = $baseEmail.'@stfrancisofassisi.tech';
        $counter = 1;

        while (User::where('email', $email)->exists()) {
            $email = $baseEmail.$counter.'@stfrancisofassisi.tech';
            $counter++;
        }

        return $email;
    }

    /**
     * Send credentials email
     */
    private function sendCredentialsEmail(string $name, string $email, string $password, string $role): bool
    {
        try {
            Mail::to($email)->send(new StaffCredentialsCreated($name, $email, $password, $role));

            Log::info('Credentials email sent', ['email' => $email]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send credentials email', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send credentials SMS (kept under 159 characters)
     */
    private function sendCredentialsSms(string $name, string $email, string $password, string $phone, int $userId): bool
    {
        try {
            $smsService = app(SmsService::class);

            // Format message for SMS - keeping it under 159 characters
            // Total: approximately 140-150 characters depending on email/password length
            $message = "Welcome {$name}! Your St Francis Portal account is ready.\n".
                       "Email: {$email}\n".
                       "Pass: {$password}\n".
                       "Login: ".config('app.url')."/admin";

            $sent = $smsService->send(
                $message,
                $phone,
                'staff_credentials',
                $userId
            );

            if ($sent) {
                Log::info('Credentials SMS sent', ['phone' => $phone, 'length' => strlen($message)]);
            }

            return $sent;
        } catch (\Exception $e) {
            Log::error('Failed to send credentials SMS', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Assign all subjects to a primary teacher's grade
     */
    private function assignAllSubjectsToGrade($teacher): void
    {
        if (! $teacher->grade || ! $teacher->classSection) {
            return;
        }

        // Update the class section to set this teacher as the class teacher
        $teacher->classSection->update([
            'class_teacher_id' => $teacher->id,
        ]);

        $currentAcademicYear = AcademicYear::where('is_active', true)->first();
        $subjects = $teacher->grade->subjects()->where('is_active', true)->get();

        // Clear existing assignments for this teacher
        $teacher->subjectTeachings()->delete();

        // Assign all subjects
        foreach ($subjects as $subject) {
            $teacher->subjectTeachings()->create([
                'subject_id' => $subject->id,
                'class_section_id' => $teacher->class_section_id,
                'academic_year_id' => $currentAcademicYear?->id,
            ]);
        }

        // Sync the subjects relationship
        $teacher->subjects()->sync($subjects->pluck('id')->toArray());

        Notification::make()
            ->title('Teacher Created Successfully')
            ->body('Assigned to '.$subjects->count().' subjects for '.$teacher->grade->name)
            ->success()
            ->send();
    }

    /**
     * Handle secondary teacher assignment - explicit subject/class assignments
     */
    private function handleSecondaryTeacher($teacher, $data): void
    {
        // Clear existing subject teachings for this teacher
        $teacher->subjectTeachings()->delete();

        // Get current academic year
        $currentAcademicYear = AcademicYear::where('is_active', true)->first();
        $academicYearId = $currentAcademicYear ? $currentAcademicYear->id : null;

        $assignedCombinations = [];

        // Add new subject teachings
        foreach ($data['subject_classes'] as $assignment) {
            $classSection = ClassSection::find($assignment['class_section_id']);
            $subject = \App\Models\Subject::find($assignment['subject_id']);

            if ($classSection && $subject) {
                $teacher->subjectTeachings()->create([
                    'subject_id' => $assignment['subject_id'],
                    'class_section_id' => $assignment['class_section_id'],
                    'academic_year_id' => $academicYearId,
                ]);

                $assignedCombinations[] = $subject->name.' ('.$classSection->grade->name.' '.$classSection->name.')';
            }
        }

        // Sync subjects (unique subjects this teacher teaches)
        $subjectIds = collect($data['subject_classes'])->pluck('subject_id')->unique()->toArray();
        $teacher->subjects()->sync($subjectIds);

        // Show success notification
        Notification::make()
            ->title('Secondary Teacher Successfully Created')
            ->body('Assigned to: '.implode(', ', $assignedCombinations))
            ->success()
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
