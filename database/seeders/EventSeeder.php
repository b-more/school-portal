<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Event;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = Employee::all();

        // Past events
        $pastEvents = [
            [
                'title' => 'Annual Sports Day',
                'description' => 'A day of competitive sports and athletics bringing together students, teachers, and parents. Events include track and field, team sports, and fun games for all ages.',
                'category' => 'sports',
                'status' => 'completed',
                'start_date' => now()->subMonths(3),
                'end_date' => now()->subMonths(3)->addHours(8),
                'location' => 'School Sports Ground',
            ],
            [
                'title' => 'Science Fair',
                'description' => 'Students showcase their science projects and experiments. Prizes will be awarded for the most innovative, educational, and well-presented projects.',
                'category' => 'academic',
                'status' => 'completed',
                'start_date' => now()->subMonths(2),
                'end_date' => now()->subMonths(2)->addHours(6),
                'location' => 'School Hall',
            ],
            [
                'title' => 'Parent-Teacher Conference',
                'description' => 'An opportunity for parents to meet with teachers to discuss student progress, address concerns, and set goals for the upcoming term.',
                'category' => 'academic',
                'status' => 'completed',
                'start_date' => now()->subMonths(1),
                'end_date' => now()->subMonths(1)->addHours(4),
                'location' => 'Classrooms',
            ],
            [
                'title' => 'Cultural Day Celebration',
                'description' => 'A celebration of diversity featuring performances, traditional foods, costumes, and displays representing various cultures from around the world.',
                'category' => 'cultural',
                'status' => 'completed',
                'start_date' => now()->subWeeks(3),
                'end_date' => now()->subWeeks(3)->addHours(5),
                'location' => 'School Grounds',
            ],
        ];

        // Upcoming events
        $upcomingEvents = [
            [
                'title' => 'End of Term Ceremony',
                'description' => 'Celebrating student achievements and marking the end of the term. Awards will be presented to outstanding students in academics, sports, and community service.',
                'category' => 'academic',
                'status' => 'upcoming',
                'start_date' => now()->addWeeks(2),
                'end_date' => now()->addWeeks(2)->addHours(3),
                'location' => 'School Assembly Hall',
            ],
            [
                'title' => 'Career Day',
                'description' => 'Professionals from various fields will visit the school to speak about their careers and provide guidance to students. This event is particularly important for Grade 8 and 9 students.',
                'category' => 'academic',
                'status' => 'upcoming',
                'start_date' => now()->addWeeks(3),
                'end_date' => now()->addWeeks(3)->addHours(6),
                'location' => 'School Hall',
                'target_grades' => ['Grade 8', 'Grade 9'],
            ],
            [
                'title' => 'School Concert',
                'description' => 'An evening of music, drama, and dance performances by our talented students. All parents and family members are welcome to attend.',
                'category' => 'cultural',
                'status' => 'upcoming',
                'start_date' => now()->addMonths(1),
                'end_date' => now()->addMonths(1)->addHours(3),
                'location' => 'School Auditorium',
            ],
            [
                'title' => 'Sports Tournament',
                'description' => 'Inter-school sports competition featuring football, netball, volleyball, and athletics. Come support our school teams!',
                'category' => 'sports',
                'status' => 'upcoming',
                'start_date' => now()->addMonths(1)->addWeeks(2),
                'end_date' => now()->addMonths(1)->addWeeks(2)->addDays(2),
                'location' => 'School Sports Ground and neighboring schools',
            ],
            [
                'title' => 'Annual Prize Giving Day',
                'description' => 'A special ceremony recognizing academic excellence and achievement across all grades. Parents of award recipients will receive special invitations.',
                'category' => 'academic',
                'status' => 'upcoming',
                'start_date' => now()->addMonths(2),
                'end_date' => now()->addMonths(2)->addHours(4),
                'location' => 'School Main Hall',
            ],
        ];

        // Cancelled event
        $cancelledEvents = [
            [
                'title' => 'Swimming Gala',
                'description' => 'CANCELLED DUE TO POOL MAINTENANCE. The annual swimming competition showcasing students\' swimming abilities across different age groups and swimming styles.',
                'category' => 'sports',
                'status' => 'cancelled',
                'start_date' => now()->addWeeks(1),
                'end_date' => now()->addWeeks(1)->addHours(4),
                'location' => 'School Swimming Pool',
            ],
        ];

        // Ongoing event
        $ongoingEvents = [
            [
                'title' => 'Book Week',
                'description' => 'A week-long celebration of reading and literature. Activities include book fair, author visits, reading competitions, and storytelling sessions.',
                'category' => 'academic',
                'status' => 'ongoing',
                'start_date' => now()->subDays(2),
                'end_date' => now()->addDays(3),
                'location' => 'School Library and Classrooms',
            ],
        ];

        $allEvents = array_merge($pastEvents, $upcomingEvents, $cancelledEvents, $ongoingEvents);

        foreach ($allEvents as $eventData) {
            // Create slug
            $slug = Str::slug($eventData['title']);

            // Set random organizer
            $organizer = $employees->random();

            // Create event
            Event::create([
                'title' => $eventData['title'],
                'description' => $eventData['description'],
                'slug' => $slug,
                'start_date' => $eventData['start_date'],
                'end_date' => $eventData['end_date'],
                'location' => $eventData['location'],
                'category' => $eventData['category'],
                'status' => $eventData['status'],
                'organizer_id' => $organizer->id,
                'notify_parents' => isset($eventData['target_grades']) ? true : fake()->boolean(70),
                'target_grades' => $eventData['target_grades'] ?? null,
            ]);
        }
    }
}
