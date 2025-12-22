<?php

namespace App\Providers;

use App\Models\Employee;
use App\Models\Event;
use App\Models\HomeworkSubmission;
use App\Models\PaymentTransaction;
use App\Models\Student;
use App\Models\StudentFee;
use App\Observers\EmployeeObserver;
use App\Observers\EventObserver;
use App\Observers\HomeworkSubmissionObserver;
use App\Observers\PaymentTransactionObserver;
use App\Observers\StudentObserver;
use App\Observers\StudentFeeObserver;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') === 'production' || request()->header('X-Forwarded-Proto') === 'https') {
            URL::forceScheme('https');
        }

        // Register model observers for admin notifications
        Student::observe(StudentObserver::class);
        StudentFee::observe(StudentFeeObserver::class);
        HomeworkSubmission::observe(HomeworkSubmissionObserver::class);
        Employee::observe(EmployeeObserver::class);
        Event::observe(EventObserver::class);

        // Register accounting integration observer
        PaymentTransaction::observe(PaymentTransactionObserver::class);
    }
}
