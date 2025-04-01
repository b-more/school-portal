<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Homework;
use App\Models\Result;
use App\Models\SmsLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class SmsLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get reference records for testing
        $admin = User::where('email', 'admin@example.com')->first();

        $homeworks = Homework::all();
        $results = Result::all();
        $events = Event::all();

        // Create sample SMS logs for homework notifications
        foreach ($homeworks as $homework) {
            // Create 2-5 logs per homework
            $logsCount = rand(2, 5);

            for ($i = 0; $i < $logsCount; $i++) {
                $status = fake()->randomElement(['sent', 'sent', 'sent', 'delivered', 'failed']);
                $phoneNumber = '26097' . fake()->numberBetween(1000000, 9999999);

                SmsLog::create([
                    'recipient' => $phoneNumber,
                    'message' => "New homework assigned: {$homework->title}. Due: {$homework->due_date->format('d/m/Y')}. Check: https://stfrancisofassisi.site",
                    'status' => $status,
                    'message_type' => 'homework_notification',
                    'reference_id' => $homework->id,
                    'cost' => $status !== 'failed' ? 1.00 : null,
                    'provider_reference' => $status !== 'failed' ? 'MSG-' . fake()->numberBetween(100000, 999999) : null,
                    'error_message' => $status === 'failed' ? fake()->randomElement([
                        'Failed to connect to SMS provider',
                        'Invalid phone number format',
                        'Insufficient SMS credits',
                        'Network timeout'
                    ]) : null,
                    'sent_by' => $admin->id,
                    'created_at' => $homework->created_at->addMinutes(rand(5, 60)),
                ]);
            }
        }

        // Create sample SMS logs for result notifications
        foreach ($results as $result) {
            // Only create logs for some results
            if (fake()->boolean(70)) {
                $status = fake()->randomElement(['sent', 'sent', 'delivered', 'failed']);

                // Get the student's parent phone number if available
                $phoneNumber = $result->student->parentGuardian->phone ?? '26097' . fake()->numberBetween(1000000, 9999999);

                SmsLog::create([
                    'recipient' => $phoneNumber,
                    'message' => "Result update for {$result->student->name}: {$result->subject->name} {$result->exam_type}. Grade: {$result->grade}. Details: https://stfrancisofassisi.site",
                    'status' => $status,
                    'message_type' => 'result_notification',
                    'reference_id' => $result->id,
                    'cost' => $status !== 'failed' ? 1.00 : null,
                    'provider_reference' => $status !== 'failed' ? 'MSG-' . fake()->numberBetween(100000, 999999) : null,
                    'error_message' => $status === 'failed' ? fake()->randomElement([
                        'Failed to connect to SMS provider',
                        'Invalid phone number format',
                        'Insufficient SMS credits',
                        'Network timeout'
                    ]) : null,
                    'sent_by' => $admin->id,
                    'created_at' => $result->created_at->addMinutes(rand(5, 60)),
                ]);
            }
        }

        // Create sample SMS logs for event notifications
        foreach ($events as $event) {
            // Create more logs for upcoming events
            $logsCount = $event->status === 'upcoming' ? rand(10, 20) : rand(3, 8);

            for ($i = 0; $i < $logsCount; $i++) {
                $status = fake()->randomElement(['sent', 'sent', 'sent', 'delivered', 'failed']);
                $phoneNumber = '26097' . fake()->numberBetween(1000000, 9999999);

                SmsLog::create([
                    'recipient' => $phoneNumber,
                    'message' => "School Event: \"{$event->title}\" on {$event->start_date->format('d/m/Y')} at {$event->location}. Details: https://stfrancisofassisi.site",
                    'status' => $status,
                    'message_type' => 'event_notification',
                    'reference_id' => $event->id,
                    'cost' => $status !== 'failed' ? 1.00 : null,
                    'provider_reference' => $status !== 'failed' ? 'MSG-' . fake()->numberBetween(100000, 999999) : null,
                    'error_message' => $status === 'failed' ? fake()->randomElement([
                        'Failed to connect to SMS provider',
                        'Invalid phone number format',
                        'Insufficient SMS credits',
                        'Network timeout'
                    ]) : null,
                    'sent_by' => $admin->id,
                    'created_at' => $event->created_at->addMinutes(rand(5, 60)),
                ]);
            }
        }

        // Create some general messages
        for ($i = 0; $i < 15; $i++) {
            $status = fake()->randomElement(['sent', 'sent', 'sent', 'delivered', 'failed']);
            $phoneNumber = '26097' . fake()->numberBetween(1000000, 9999999);

            SmsLog::create([
                'recipient' => $phoneNumber,
                'message' => fake()->randomElement([
                    "Reminder: School closes for mid-term break on Friday. Classes resume on Monday next week.",
                    "Important: PTA meeting scheduled for this Saturday at 10:00 AM in the school hall.",
                    "Please note that school fees for Term 2 are due by the end of this month.",
                    "Due to the weather forecast, all outdoor activities are cancelled tomorrow.",
                    "The school bus will operate on a revised schedule tomorrow due to road works."
                ]),
                'status' => $status,
                'message_type' => 'general',
                'reference_id' => null,
                'cost' => $status !== 'failed' ? 1.00 : null,
                'provider_reference' => $status !== 'failed' ? 'MSG-' . fake()->numberBetween(100000, 999999) : null,
                'error_message' => $status === 'failed' ? fake()->randomElement([
                    'Failed to connect to SMS provider',
                    'Invalid phone number format',
                    'Insufficient SMS credits',
                    'Network timeout'
                ]) : null,
                'sent_by' => $admin->id,
                'created_at' => fake()->dateTimeBetween('-3 months', 'now'),
            ]);
        }
    }
}
