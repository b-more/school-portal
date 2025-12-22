<?php

namespace App\Filament\Resources\TimetablePeriodResource\Pages;

use App\Filament\Resources\TimetablePeriodResource;
use App\Models\AcademicYear;
use App\Models\TimetablePeriod;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListTimetablePeriods extends ListRecords
{
    protected static string $resource = TimetablePeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('quickSetup')
                ->label('Quick Setup')
                ->icon('heroicon-o-bolt')
                ->color('success')
                ->modalHeading('Quick Timetable Setup')
                ->modalDescription('Generate a complete school day schedule with breaks.')
                ->modalWidth('xl')
                ->form([
                    Forms\Components\Select::make('academic_year_id')
                        ->label('Academic Year')
                        ->options(AcademicYear::orderBy('name', 'desc')->pluck('name', 'id'))
                        ->default(fn() => AcademicYear::current()?->id)
                        ->required(),

                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TimePicker::make('first_period_start')
                                ->label('First Period Starts')
                                ->default('07:30')
                                ->seconds(false)
                                ->required(),

                            Forms\Components\TextInput::make('period_duration')
                                ->label('Period Duration')
                                ->numeric()
                                ->default(40)
                                ->suffix('minutes')
                                ->required(),
                        ]),

                    Forms\Components\Section::make('Tea Break')
                        ->schema([
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\TimePicker::make('tea_break_start')
                                        ->label('Tea Break Starts')
                                        ->default('10:10')
                                        ->seconds(false)
                                        ->required(),

                                    Forms\Components\TimePicker::make('tea_break_end')
                                        ->label('Tea Break Ends')
                                        ->default('10:30')
                                        ->seconds(false)
                                        ->required(),
                                ]),
                        ])
                        ->compact(),

                    Forms\Components\Section::make('Lunch Break')
                        ->schema([
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\TimePicker::make('lunch_break_start')
                                        ->label('Lunch Break Starts')
                                        ->default('13:10')
                                        ->seconds(false)
                                        ->required(),

                                    Forms\Components\TimePicker::make('lunch_break_end')
                                        ->label('Lunch Break Ends')
                                        ->default('14:00')
                                        ->seconds(false)
                                        ->required(),
                                ]),
                        ])
                        ->compact(),

                    Forms\Components\TimePicker::make('school_end_time')
                        ->label('School Ends (Knock Off)')
                        ->default('15:30')
                        ->seconds(false)
                        ->required(),

                    Forms\Components\Toggle::make('clear_existing')
                        ->label('Replace existing periods for this academic year')
                        ->default(false),
                ])
                ->action(function (array $data) {
                    $this->generatePeriods($data);
                }),

            Actions\CreateAction::make()
                ->label('Add Single Period'),
        ];
    }

    protected function generatePeriods(array $data): void
    {
        $academicYearId = $data['academic_year_id'];
        $periodDuration = (int) $data['period_duration'];

        // Check for existing periods
        $existingCount = TimetablePeriod::where('academic_year_id', $academicYearId)->count();

        if ($existingCount > 0 && !$data['clear_existing']) {
            Notification::make()
                ->title('Periods Already Exist')
                ->body("There are already {$existingCount} periods for this academic year. Enable 'Replace existing periods' to replace them.")
                ->warning()
                ->persistent()
                ->send();
            return;
        }

        // Clear existing periods if requested
        if ($data['clear_existing']) {
            TimetablePeriod::where('academic_year_id', $academicYearId)->delete();
        }

        // Parse times
        $currentTime = Carbon::parse($data['first_period_start']);
        $teaBreakStart = Carbon::parse($data['tea_break_start']);
        $teaBreakEnd = Carbon::parse($data['tea_break_end']);
        $lunchBreakStart = Carbon::parse($data['lunch_break_start']);
        $lunchBreakEnd = Carbon::parse($data['lunch_break_end']);
        $schoolEndTime = Carbon::parse($data['school_end_time']);

        $order = 1;
        $periodNumber = 1;
        $periodsCreated = [];

        // Generate periods before Tea Break
        while ($currentTime->copy()->addMinutes($periodDuration)->lte($teaBreakStart)) {
            $endTime = $currentTime->copy()->addMinutes($periodDuration);

            $periodsCreated[] = TimetablePeriod::create([
                'academic_year_id' => $academicYearId,
                'name' => "Period {$periodNumber}",
                'short_name' => "P{$periodNumber}",
                'type' => TimetablePeriod::TYPE_LESSON,
                'start_time' => $currentTime->format('H:i'),
                'end_time' => $endTime->format('H:i'),
                'order' => $order++,
                'is_active' => true,
            ]);

            $currentTime = $endTime;
            $periodNumber++;
        }

        // Tea Break
        $periodsCreated[] = TimetablePeriod::create([
            'academic_year_id' => $academicYearId,
            'name' => 'Tea Break',
            'short_name' => 'TEA',
            'type' => TimetablePeriod::TYPE_TEA_BREAK,
            'start_time' => $teaBreakStart->format('H:i'),
            'end_time' => $teaBreakEnd->format('H:i'),
            'order' => $order++,
            'is_active' => true,
        ]);

        $currentTime = $teaBreakEnd->copy();

        // Generate periods between Tea Break and Lunch
        while ($currentTime->copy()->addMinutes($periodDuration)->lte($lunchBreakStart)) {
            $endTime = $currentTime->copy()->addMinutes($periodDuration);

            $periodsCreated[] = TimetablePeriod::create([
                'academic_year_id' => $academicYearId,
                'name' => "Period {$periodNumber}",
                'short_name' => "P{$periodNumber}",
                'type' => TimetablePeriod::TYPE_LESSON,
                'start_time' => $currentTime->format('H:i'),
                'end_time' => $endTime->format('H:i'),
                'order' => $order++,
                'is_active' => true,
            ]);

            $currentTime = $endTime;
            $periodNumber++;
        }

        // Lunch Break
        $periodsCreated[] = TimetablePeriod::create([
            'academic_year_id' => $academicYearId,
            'name' => 'Lunch Break',
            'short_name' => 'LUN',
            'type' => TimetablePeriod::TYPE_LUNCH_BREAK,
            'start_time' => $lunchBreakStart->format('H:i'),
            'end_time' => $lunchBreakEnd->format('H:i'),
            'order' => $order++,
            'is_active' => true,
        ]);

        $currentTime = $lunchBreakEnd->copy();

        // Generate periods after Lunch until school ends
        while ($currentTime->copy()->addMinutes($periodDuration)->lte($schoolEndTime)) {
            $endTime = $currentTime->copy()->addMinutes($periodDuration);

            $periodsCreated[] = TimetablePeriod::create([
                'academic_year_id' => $academicYearId,
                'name' => "Period {$periodNumber}",
                'short_name' => "P{$periodNumber}",
                'type' => TimetablePeriod::TYPE_LESSON,
                'start_time' => $currentTime->format('H:i'),
                'end_time' => $endTime->format('H:i'),
                'order' => $order++,
                'is_active' => true,
            ]);

            $currentTime = $endTime;
            $periodNumber++;
        }

        $totalPeriods = count($periodsCreated);
        $totalLessons = $periodNumber - 1;

        Notification::make()
            ->title('Periods Created Successfully!')
            ->body("Created {$totalPeriods} time slots ({$totalLessons} lesson periods + 2 breaks).")
            ->success()
            ->send();
    }
}
