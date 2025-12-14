<?php

namespace App\Console\Commands;

use App\Constants\RoleConstants;
use App\Models\ParentGuardian;
use App\Models\Student;
use App\Models\User;
use App\Models\UserCredential;
use App\Services\SmsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SetupStudentParentAccounts extends Command
{
    protected $signature = 'accounts:setup
                            {--students : Create accounts for students only}
                            {--parents : Create accounts for parents only}
                            {--link : Link students to parents based on matching criteria}
                            {--send-sms : Send credentials via SMS}
                            {--grade= : Filter by grade ID}
                            {--dry-run : Preview without making changes}';

    protected $description = 'Setup student and parent accounts with login credentials';

    protected int $studentsCreated = 0;
    protected int $parentsCreated = 0;
    protected int $studentsLinked = 0;
    protected int $smssSent = 0;
    protected int $errors = 0;

    protected array $credentials = [];

    public function handle()
    {
        $this->info('========================================');
        $this->info('  Student & Parent Account Setup Tool  ');
        $this->info('========================================');
        $this->newLine();

        $isDryRun = $this->option('dry-run');
        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        // Show current status
        $this->showCurrentStatus();

        // Determine what to do
        $doStudents = $this->option('students') || (!$this->option('parents') && !$this->option('link'));
        $doParents = $this->option('parents') || (!$this->option('students') && !$this->option('link'));
        $doLink = $this->option('link');

        if ($doStudents) {
            $this->createStudentAccounts($isDryRun);
        }

        if ($doParents) {
            $this->createParentAccounts($isDryRun);
        }

        if ($doLink) {
            $this->warn('Link feature requires parent data in student records or a separate linking CSV.');
            $this->info('Use the CSV import with parent columns to link students to parents.');
        }

        // Summary
        $this->newLine();
        $this->info('========================================');
        $this->info('              SUMMARY                   ');
        $this->info('========================================');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Student accounts created', $this->studentsCreated],
                ['Parent accounts created', $this->parentsCreated],
                ['SMS notifications sent', $this->smssSent],
                ['Errors', $this->errors],
            ]
        );

        // Output credentials to file if any were created
        if (!$isDryRun && !empty($this->credentials)) {
            $this->exportCredentials();
        }

        return 0;
    }

    protected function showCurrentStatus(): void
    {
        $totalStudents = Student::count();
        $studentsWithAccounts = Student::whereNotNull('user_id')->count();
        $studentsWithParents = Student::whereNotNull('parent_guardian_id')->count();
        $totalParents = ParentGuardian::count();
        $parentsWithAccounts = ParentGuardian::whereNotNull('user_id')->count();

        $this->info('Current Status:');
        $this->table(
            ['Entity', 'Total', 'With Accounts', 'Without Accounts'],
            [
                ['Students', $totalStudents, $studentsWithAccounts, $totalStudents - $studentsWithAccounts],
                ['Students with Parents', $studentsWithParents, '-', $totalStudents - $studentsWithParents],
                ['Parents', $totalParents, $parentsWithAccounts, $totalParents - $parentsWithAccounts],
            ]
        );
        $this->newLine();
    }

    protected function createStudentAccounts(bool $isDryRun): void
    {
        $this->info('Creating Student Accounts...');
        $this->newLine();

        $query = Student::whereNull('user_id')
            ->where('enrollment_status', 'active');

        if ($gradeId = $this->option('grade')) {
            $query->where('grade_id', $gradeId);
        }

        $students = $query->with(['grade', 'parentGuardian'])->get();

        if ($students->isEmpty()) {
            $this->info('No students found without accounts.');
            return;
        }

        $this->info("Found {$students->count()} students without accounts.");

        $progressBar = $this->output->createProgressBar($students->count());
        $progressBar->start();

        foreach ($students as $student) {
            try {
                if (!$isDryRun) {
                    $this->createStudentAccount($student);
                }
                $this->studentsCreated++;
            } catch (\Exception $e) {
                $this->errors++;
                Log::error('Failed to create student account', [
                    'student_id' => $student->id,
                    'error' => $e->getMessage(),
                ]);
            }
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);
    }

    protected function createStudentAccount(Student $student): void
    {
        DB::transaction(function () use ($student) {
            // Generate email
            $email = $this->generateStudentEmail($student);

            // Generate password
            $password = Str::password(10);

            // Create user
            $user = User::create([
                'name' => $student->name,
                'email' => $email,
                'password' => Hash::make($password),
                'role_id' => RoleConstants::STUDENT,
                'status' => 'active',
            ]);

            // Link to student
            $student->update(['user_id' => $user->id]);

            // Store credentials
            UserCredential::create([
                'user_id' => $user->id,
                'username' => $email,
                'password' => $password,
                'is_sent' => false,
                'delivery_method' => 'pending',
            ]);

            // Store for export
            $this->credentials[] = [
                'type' => 'Student',
                'name' => $student->name,
                'student_id' => $student->student_id_number,
                'grade' => $student->grade?->name ?? 'N/A',
                'email' => $email,
                'password' => $password,
                'parent_phone' => $student->parentGuardian?->phone ?? 'N/A',
            ];

            // Send SMS if requested
            if ($this->option('send-sms') && $student->parentGuardian?->phone) {
                $this->sendStudentCredentialsSms($student, $email, $password);
            }
        });
    }

    protected function createParentAccounts(bool $isDryRun): void
    {
        $this->info('Creating Parent Accounts...');
        $this->newLine();

        $parents = ParentGuardian::whereNull('user_id')
            ->whereNotNull('phone')
            ->get();

        if ($parents->isEmpty()) {
            $this->info('No parents found without accounts.');
            return;
        }

        $this->info("Found {$parents->count()} parents without accounts.");

        $progressBar = $this->output->createProgressBar($parents->count());
        $progressBar->start();

        foreach ($parents as $parent) {
            try {
                if (!$isDryRun) {
                    $this->createParentAccount($parent);
                }
                $this->parentsCreated++;
            } catch (\Exception $e) {
                $this->errors++;
                Log::error('Failed to create parent account', [
                    'parent_id' => $parent->id,
                    'error' => $e->getMessage(),
                ]);
            }
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);
    }

    protected function createParentAccount(ParentGuardian $parent): void
    {
        DB::transaction(function () use ($parent) {
            // Generate email from phone or name
            $email = $this->generateParentEmail($parent);

            // Generate password
            $password = Str::password(10);

            // Create user
            $user = User::create([
                'name' => $parent->name,
                'email' => $email,
                'phone' => $parent->phone,
                'password' => Hash::make($password),
                'role_id' => RoleConstants::PARENT,
                'status' => 'active',
            ]);

            // Link to parent
            $parent->update(['user_id' => $user->id]);

            // Store credentials
            UserCredential::create([
                'user_id' => $user->id,
                'username' => $email,
                'password' => $password,
                'is_sent' => false,
                'delivery_method' => 'pending',
            ]);

            // Get children names
            $children = Student::where('parent_guardian_id', $parent->id)->pluck('name')->implode(', ');

            // Store for export
            $this->credentials[] = [
                'type' => 'Parent',
                'name' => $parent->name,
                'student_id' => '-',
                'grade' => '-',
                'email' => $email,
                'password' => $password,
                'parent_phone' => $parent->phone,
                'children' => $children ?: 'None linked',
            ];

            // Send SMS if requested
            if ($this->option('send-sms') && $parent->phone) {
                $this->sendParentCredentialsSms($parent, $email, $password);
            }
        });
    }

    protected function generateStudentEmail(Student $student): string
    {
        // Use student ID if available
        if ($student->student_id_number) {
            $baseEmail = strtolower(str_replace(['STU-', '-'], '', $student->student_id_number));
            $email = "student{$baseEmail}@stfrancisofassisi.tech";

            if (!User::where('email', $email)->exists()) {
                return $email;
            }
        }

        // Fall back to name-based email
        $nameParts = explode(' ', $student->name);
        $firstName = strtolower(preg_replace('/[^a-z]/', '', $nameParts[0] ?? 'student'));
        $lastName = strtolower(preg_replace('/[^a-z]/', '', end($nameParts) ?? ''));

        $baseEmail = "{$firstName}.{$lastName}";
        $email = "{$baseEmail}@student.stfrancisofassisi.tech";
        $counter = 1;

        while (User::where('email', $email)->exists()) {
            $email = "{$baseEmail}{$counter}@student.stfrancisofassisi.tech";
            $counter++;
        }

        return $email;
    }

    protected function generateParentEmail(ParentGuardian $parent): string
    {
        // Use phone number as base
        if ($parent->phone) {
            $phone = preg_replace('/[^0-9]/', '', $parent->phone);
            $phone = substr($phone, -9); // Last 9 digits
            $email = "parent{$phone}@stfrancisofassisi.tech";

            if (!User::where('email', $email)->exists()) {
                return $email;
            }
        }

        // Fall back to name-based email
        $nameParts = explode(' ', $parent->name ?? 'Parent');
        $firstName = strtolower(preg_replace('/[^a-z]/', '', $nameParts[0] ?? 'parent'));
        $lastName = strtolower(preg_replace('/[^a-z]/', '', end($nameParts) ?? ''));

        $baseEmail = "{$firstName}.{$lastName}";
        $email = "{$baseEmail}@parent.stfrancisofassisi.tech";
        $counter = 1;

        while (User::where('email', $email)->exists()) {
            $email = "{$baseEmail}{$counter}@parent.stfrancisofassisi.tech";
            $counter++;
        }

        return $email;
    }

    protected function sendStudentCredentialsSms(Student $student, string $email, string $password): void
    {
        try {
            $message = "St Francis Portal\n" .
                "Student: {$student->name}\n" .
                "Login: {$email}\n" .
                "Pass: {$password}\n" .
                config('app.url') . "/admin";

            $smsService = app(SmsService::class);
            $sent = $smsService->send($message, $student->parentGuardian->phone, 'student_credentials');

            if ($sent) {
                $this->smssSent++;
                UserCredential::where('username', $email)->update([
                    'is_sent' => true,
                    'sent_at' => now(),
                    'delivery_method' => 'sms',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('SMS send failed', ['error' => $e->getMessage()]);
        }
    }

    protected function sendParentCredentialsSms(ParentGuardian $parent, string $email, string $password): void
    {
        try {
            $message = "St Francis Portal\n" .
                "Parent: {$parent->name}\n" .
                "Login: {$email}\n" .
                "Pass: {$password}\n" .
                config('app.url') . "/admin";

            $smsService = app(SmsService::class);
            $sent = $smsService->send($message, $parent->phone, 'parent_credentials');

            if ($sent) {
                $this->smssSent++;
                UserCredential::where('username', $email)->update([
                    'is_sent' => true,
                    'sent_at' => now(),
                    'delivery_method' => 'sms',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('SMS send failed', ['error' => $e->getMessage()]);
        }
    }

    protected function exportCredentials(): void
    {
        $filename = storage_path('app/credentials_' . date('Y-m-d_His') . '.csv');

        $fp = fopen($filename, 'w');
        fputcsv($fp, ['Type', 'Name', 'Student ID', 'Grade', 'Email', 'Password', 'Phone', 'Children']);

        foreach ($this->credentials as $cred) {
            fputcsv($fp, [
                $cred['type'],
                $cred['name'],
                $cred['student_id'] ?? '-',
                $cred['grade'] ?? '-',
                $cred['email'],
                $cred['password'],
                $cred['parent_phone'] ?? '-',
                $cred['children'] ?? '-',
            ]);
        }

        fclose($fp);

        $this->newLine();
        $this->info("Credentials exported to: {$filename}");
        $this->warn("IMPORTANT: Keep this file secure and delete after distributing credentials!");
    }
}
