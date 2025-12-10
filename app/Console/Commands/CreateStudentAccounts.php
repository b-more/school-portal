<?php

namespace App\Console\Commands;

use App\Constants\RoleConstants;
use App\Mail\StudentCredentialsCreated;
use App\Models\Student;
use App\Models\User;
use App\Models\UserCredential;
use App\Services\SmsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CreateStudentAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'students:create-accounts {--student-id=* : Specific student IDs to create accounts for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create user accounts for students who don\'t have login credentials';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creating student user accounts...');

        // Get students without user accounts
        $query = Student::whereNull('user_id')
            ->where('enrollment_status', 'active')
            ->with(['grade', 'parentGuardian']);

        // If specific student IDs provided, filter to those
        $studentIds = $this->option('student-id');
        if (!empty($studentIds)) {
            $query->whereIn('id', $studentIds);
        }

        $students = $query->get();

        if ($students->isEmpty()) {
            $this->info('No students found without user accounts.');
            return 0;
        }

        $this->info("Found {$students->count()} students without accounts.");

        $successCount = 0;
        $errorCount = 0;

        foreach ($students as $student) {
            $this->line("\nProcessing: {$student->name}");

            try {
                DB::transaction(function () use ($student, &$successCount) {
                    // Generate email from student name and ID
                    $email = $this->generateEmail($student->name, $student->student_id_number);

                    // Generate a secure random password
                    $password = Str::password(12);

                    // Create user account
                    $user = User::create([
                        'name' => $student->name,
                        'email' => $email,
                        'phone' => null, // Students typically use parent's phone
                        'password' => Hash::make($password),
                        'role_id' => RoleConstants::STUDENT,
                        'status' => 'active',
                    ]);

                    // Link student to user
                    $student->update(['user_id' => $user->id]);

                    // Store credentials
                    UserCredential::create([
                        'user_id' => $user->id,
                        'username' => $email,
                        'password' => $password,
                        'is_sent' => false,
                        'delivery_method' => 'email_and_sms',
                    ]);

                    $this->info("  ✓ Created account: {$email}");
                    $this->info("  ✓ Password: {$password}");

                    // Send credentials to parent via SMS
                    $emailSent = false;
                    $smsSent = false;

                    if ($student->parentGuardian && $student->parentGuardian->phone) {
                        try {
                            $smsService = app(SmsService::class);

                            $message = "St Francis Portal - Student Account Created\n\n".
                                       "Student: {$student->name}\n".
                                       "Email: {$email}\n".
                                       "Password: {$password}\n\n".
                                       'Login at: '.config('app.url')."/admin\n\n".
                                       'Please help your child login and change password.';

                            $smsSent = $smsService->send(
                                $message,
                                $student->parentGuardian->phone,
                                'student_credentials',
                                $user->id
                            );

                            if ($smsSent) {
                                $this->info("  ✓ SMS sent to parent: {$student->parentGuardian->phone}");
                            } else {
                                $this->warn("  ✗ Failed to send SMS to parent");
                            }
                        } catch (\Exception $e) {
                            $this->error("  ✗ SMS error: {$e->getMessage()}");
                            Log::error('Failed to send student credentials via SMS', [
                                'student_id' => $student->id,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    } else {
                        $this->warn("  ⚠ No parent phone number available");
                    }

                    // Update credential record
                    if ($smsSent) {
                        UserCredential::where('username', $email)->update([
                            'is_sent' => true,
                            'sent_at' => now(),
                            'delivery_method' => 'sms',
                        ]);
                    }

                    $successCount++;
                });
            } catch (\Exception $e) {
                $this->error("  ✗ Error: {$e->getMessage()}");
                Log::error('Failed to create student account', [
                    'student_id' => $student->id,
                    'student_name' => $student->name,
                    'error' => $e->getMessage(),
                ]);
                $errorCount++;
            }
        }

        $this->newLine();
        $this->info("=== Summary ===");
        $this->info("Success: {$successCount}");
        if ($errorCount > 0) {
            $this->error("Errors: {$errorCount}");
        }

        return 0;
    }

    /**
     * Generate email from student name and ID
     */
    protected function generateEmail(string $name, string $studentId): string
    {
        $nameParts = explode(' ', $name);
        $firstName = strtolower($nameParts[0] ?? '');
        $lastName = strtolower(end($nameParts) ?? '');

        $baseEmail = $firstName.'.'.$lastName;
        $baseEmail = preg_replace('/[^a-z0-9\.]/', '', $baseEmail);

        $email = $baseEmail.'@student.stfrancisofassisi.tech';
        $counter = 1;

        while (User::where('email', $email)->exists()) {
            $email = $baseEmail.$counter.'@student.stfrancisofassisi.tech';
            $counter++;
        }

        return $email;
    }
}
