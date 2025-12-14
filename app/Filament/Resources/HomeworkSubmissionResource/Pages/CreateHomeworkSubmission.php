<?php

namespace App\Filament\Resources\HomeworkSubmissionResource\Pages;

use App\Constants\RoleConstants;
use App\Filament\Resources\HomeworkSubmissionResource;
use App\Models\Homework;
use App\Models\HomeworkSubmission;
use App\Models\Student;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CreateHomeworkSubmission extends CreateRecord
{
    protected static string $resource = HomeworkSubmissionResource::class;

    public function mount(): void
    {
        parent::mount();

        $user = Auth::user();

        // Pre-fill homework_id if provided in URL
        if (request()->has('homework_id')) {
            $homeworkId = request()->get('homework_id');

            // Check if student already submitted this homework
            if ($user->role_id === RoleConstants::STUDENT) {
                $student = Student::where('user_id', $user->id)->first();
                if ($student) {
                    $existingSubmission = HomeworkSubmission::where('student_id', $student->id)
                        ->where('homework_id', $homeworkId)
                        ->first();

                    if ($existingSubmission) {
                        Notification::make()
                            ->title('Already Submitted')
                            ->body('You have already submitted this homework. You can cancel your submission from the submissions list if you need to resubmit.')
                            ->warning()
                            ->persistent()
                            ->send();

                        $this->redirect(route('filament.admin.pages.student-dashboard'));
                        return;
                    }
                }
            }

            $this->form->fill([
                'homework_id' => $homeworkId,
                'status' => 'submitted',
                'submitted_at' => now(),
            ]);
        }

        // Pre-fill student_id for students
        if ($user->role_id === RoleConstants::STUDENT) {
            $student = Student::where('user_id', $user->id)->first();
            if ($student) {
                $this->form->fill([
                    'student_id' => $student->id,
                    'homework_id' => request()->get('homework_id'),
                    'submitted_at' => now(),
                    'status' => 'submitted',
                ]);
            }
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();

        // Ensure status is always set
        if (empty($data['status'])) {
            $data['status'] = 'submitted';
        }

        // Ensure submitted_at is set
        if (empty($data['submitted_at'])) {
            $data['submitted_at'] = now();
        }

        // For students, ensure their student_id is set
        if ($user->role_id === RoleConstants::STUDENT) {
            $student = Student::where('user_id', $user->id)->first();
            if ($student) {
                $data['student_id'] = $student->id;
            }
        }

        // Check for duplicate submission
        if (!empty($data['student_id']) && !empty($data['homework_id'])) {
            $existingSubmission = HomeworkSubmission::where('student_id', $data['student_id'])
                ->where('homework_id', $data['homework_id'])
                ->first();

            if ($existingSubmission) {
                Notification::make()
                    ->title('Duplicate Submission')
                    ->body('This homework has already been submitted. Cancel the existing submission first if you need to resubmit.')
                    ->danger()
                    ->send();

                throw ValidationException::withMessages([
                    'homework_id' => 'You have already submitted this homework.',
                ]);
            }
        }

        // Check if submission is late
        if (!empty($data['homework_id'])) {
            $homework = Homework::find($data['homework_id']);
            if ($homework && $homework->due_date->isPast()) {
                $data['is_late'] = true;
            }
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        $user = Auth::user();

        // Redirect students back to their dashboard after submission
        if ($user->role_id === RoleConstants::STUDENT) {
            return route('filament.admin.pages.student-dashboard');
        }

        return $this->getResource()::getUrl('index');
    }
}
