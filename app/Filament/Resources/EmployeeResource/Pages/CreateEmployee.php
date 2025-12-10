<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use App\Mail\StaffCredentialsCreated;
use App\Models\Role;
use App\Models\User;
use App\Models\UserCredential;
use App\Models\Teacher;
use App\Constants\RoleConstants;
use App\Services\SmsService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;

    /**
     * Handle the creation of a new employee and a corresponding user account
     */
    protected function handleRecordCreation(array $data): Model
    {
        // Wrap in a transaction to ensure both employee and user are created or neither
        return DB::transaction(function () use ($data) {
            // Generate email if not provided
            $email = $data['email'] ?? $this->generateEmail($data['name'], $data['employee_id']);
            $data['email'] = $email;

            // Generate a secure random password
            $password = Str::password(12);

            // Create a new user for this employee
            $user = User::create([
                'name' => $data['name'],
                'email' => $email,
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make($password),
                'role_id' => $data['role_id'] ?? null,
                'status' => 'active',
            ]);

            // Create the employee and link it to the user
            $employee = static::getModel()::create(array_merge(
                $data,
                ['user_id' => $user->id]
            ));

            // If employee is a teacher, also create teacher record
            if (isset($data['role_id']) && $data['role_id'] == RoleConstants::TEACHER) {
                Teacher::create([
                    'user_id' => $user->id,
                    'name' => $data['name'],
                    'email' => $email,
                    'phone' => $data['phone'] ?? null,
                    'employee_id' => $data['employee_id'],
                    'role_id' => $data['role_id'],
                    'grade_id' => $data['grade_id'] ?? null,
                    'class_section_id' => $data['class_section_id'] ?? null,
                    'is_class_teacher' => $data['is_class_teacher'] ?? false,
                    'is_grade_teacher' => $data['is_grade_teacher'] ?? false,
                    'specialization' => $data['specialization'] ?? null,
                    'qualification' => $data['qualification'] ?? null,
                    'is_active' => true,
                ]);

                Log::info('Teacher record created', [
                    'employee_id' => $employee->id,
                    'user_id' => $user->id,
                    'grade_id' => $data['grade_id'] ?? null,
                    'class_section_id' => $data['class_section_id'] ?? null,
                ]);
            }

            // Store credentials
            UserCredential::create([
                'user_id' => $user->id,
                'username' => $email,
                'password' => $password,
                'is_sent' => false,
                'delivery_method' => 'email_and_sms',
            ]);

            // Get role name for email
            $roleName = 'Staff Member';
            if (isset($data['role_id'])) {
                $role = Role::find($data['role_id']);
                $roleName = $role ? $role->name : 'Staff Member';
            }

            // Track notification results
            $emailSent = false;
            $smsSent = false;

            // Send the login credentials via email
            try {
                Mail::to($email)->send(new StaffCredentialsCreated(
                    $data['name'],
                    $email,
                    $password,
                    $roleName
                ));

                Log::info('Employee credentials sent via email', [
                    'employee_id' => $employee->id,
                    'email' => $email,
                ]);

                $emailSent = true;
            } catch (\Exception $e) {
                Log::error('Failed to send employee credentials via email', [
                    'employee_id' => $employee->id,
                    'email' => $email,
                    'error' => $e->getMessage(),
                ]);
            }

            // Send credentials via SMS if phone number is available
            if (! empty($data['phone'])) {
                try {
                    $smsService = app(SmsService::class);

                    // Format message for SMS
                    $message = "Welcome to St Francis Portal!\n\n".
                               "Your account has been created.\n\n".
                               "Email: {$email}\n".
                               "Password: {$password}\n\n".
                               'Login at: '.config('app.url')."/admin\n\n".
                               'Please change your password after first login.';

                    $smsSent = $smsService->send(
                        $message,
                        $data['phone'],
                        'staff_credentials',
                        $user->id
                    );

                    if ($smsSent) {
                        Log::info('Employee credentials sent via SMS', [
                            'employee_id' => $employee->id,
                            'phone' => $data['phone'],
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to send employee credentials via SMS', [
                        'employee_id' => $employee->id,
                        'phone' => $data['phone'],
                        'error' => $e->getMessage(),
                    ]);
                }
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
            $notificationBody = "Email: {$email} | Password: {$password}";
            if ($emailSent && $smsSent) {
                $notificationTitle = 'Employee Account Created Successfully';
                $notificationBody = 'Login credentials sent via EMAIL and SMS. '.$notificationBody;
                $notificationType = 'success';
            } elseif ($emailSent || $smsSent) {
                $notificationTitle = 'Employee Account Created';
                $notificationBody = 'Login credentials sent via '.($emailSent ? 'EMAIL' : 'SMS').'. '.$notificationBody;
                $notificationType = 'success';
            } else {
                $notificationTitle = 'Employee Account Created';
                $notificationBody = 'Employee created but notification delivery failed. '.$notificationBody;
                $notificationType = 'warning';
            }

            // Show notification
            Notification::make()
                ->title($notificationTitle)
                ->body($notificationBody)
                ->{$notificationType}()
                ->persistent()
                ->send();

            return $employee;
        });
    }

    /**
     * Generate email from name and employee ID
     */
    protected function generateEmail(string $name, string $employeeId): string
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
}
