<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentResource\Pages;
use App\Filament\Resources\StudentResource\RelationManagers;
use App\Models\Student;
use App\Models\User;
use App\Models\ParentGuardian;
use App\Models\UserCredential;
use App\Models\Grade;
use App\Models\Teacher;
use App\Models\SmsLog;
use App\Models\AcademicYear;
use App\Constants\RoleConstants;
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
use Illuminate\Support\Facades\Auth;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Student Management';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withCount('results')
            ->withCount('fees');

        $user = Auth::user();

        // Admin can see all students
        if ($user->role_id === RoleConstants::ADMIN) {
            return $query;
        }

        // Teachers can only see students in their classes
        if ($user->role_id === RoleConstants::TEACHER) {
            $teacher = Teacher::where('user_id', $user->id)->first();

            if ($teacher) {
                // Get students from classes where this teacher teaches
                $classSectionIds = $teacher->classSections()->pluck('id')->toArray();
                return $query->whereIn('class_section_id', $classSectionIds);
            }

            return $query->where('id', 0); // Return empty if teacher not found
        }

        // Parents can only see their own children
        if ($user->role_id === RoleConstants::PARENT) {
            $parent = $user->parentGuardian;

            if ($parent) {
                return $query->where('parent_guardian_id', $parent->id);
            }

            return $query->where('id', 0); // Return empty if parent not found
        }

        // All other roles have no access
        return $query->where('id', 0);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Personal Information')
                            ->description('Enter the student\'s basic personal details')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Full Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Enter full name as it appears on official documents'),

                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\DatePicker::make('date_of_birth')
                                            ->label('Date of Birth')
                                            ->required()
                                            ->displayFormat('d/m/Y')
                                            ->closeOnDateSelection()
                                            ->weekStartsOnSunday(),

                                        Forms\Components\TextInput::make('place_of_birth')
                                            ->label('Place of Birth')
                                            ->maxLength(255)
                                            ->placeholder('e.g., Lusaka, Zambia'),
                                    ])
                                    ->columns(2),

                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\Select::make('gender')
                                            ->options([
                                                'male' => 'Male',
                                                'female' => 'Female',
                                            ])
                                            ->required(),

                                        Forms\Components\Select::make('religious_denomination')
                                            ->options([
                                                'Christian' => 'Christian',
                                                'Catholic' => 'Catholic',
                                                'Protestant' => 'Protestant',
                                                'Pentecostal' => 'Pentecostal',
                                                'SDA' => 'Seventh Day Adventist',
                                                'Anglican' => 'Anglican',
                                                'Muslim' => 'Muslim',
                                                'Hindu' => 'Hindu',
                                                'Buddhist' => 'Buddhist',
                                                'Traditional' => 'Traditional',
                                                'Other' => 'Other',
                                                'None' => 'None',
                                            ])
                                            ->searchable()
                                            ->placeholder('Select denomination'),
                                    ])
                                    ->columns(2),

                                Forms\Components\Textarea::make('address')
                                    ->label('Residential Address')
                                    ->maxLength(255)
                                    ->placeholder('Enter full residential address')
                                    ->rows(2),
                            ]),

                        Forms\Components\Section::make('Education Details')
                            ->description('Information about student\'s current and previous education')
                            ->icon('heroicon-o-academic-cap')
                            ->schema([
                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\Card::make()
                                            ->schema([
                                                Forms\Components\Select::make('grade_id')
                                                    ->label('Grade')
                                                    ->options(function () {
                                                        return Grade::query()
                                                            ->where('is_active', true)
                                                            ->orderBy('level')
                                                            ->get()
                                                            ->pluck('name', 'id');
                                                    })
                                                    ->required()
                                                    ->searchable()
                                                    ->preload()
                                                    ->live()
                                                    ->afterStateUpdated(function ($state, callable $set) {
                                                        // Clear dependent selections when grade changes
                                                        $set('class_section_id', null);
                                                        $set('student_id_number', null);
                                                    }),

                                                Forms\Components\Select::make('class_section_id')
                                                    ->label('Class Section')
                                                    ->options(function (callable $get) {
                                                        $gradeId = $get('grade_id');
                                                        if (!$gradeId) {
                                                            return [];
                                                        }

                                                        // Get current academic year
                                                        $currentAcademicYear = AcademicYear::where('is_active', true)->first();
                                                        if (!$currentAcademicYear) {
                                                            return [];
                                                        }

                                                        // Get available class sections for the selected grade in current academic year
                                                        return \App\Models\ClassSection::where('grade_id', $gradeId)
                                                            ->where('academic_year_id', $currentAcademicYear->id)
                                                            ->where('is_active', true)
                                                            ->get()
                                                            ->mapWithKeys(function ($section) {
                                                                $currentStudents = $section->students()->count();
                                                                $capacity = $section->capacity;
                                                                $availableSpots = $capacity - $currentStudents;

                                                                $label = "{$section->name} ({$currentStudents}/{$capacity} students)";
                                                                if ($availableSpots <= 0) {
                                                                    $label .= " - FULL";
                                                                } else if ($availableSpots <= 5) {
                                                                    $label .= " - {$availableSpots} spots left";
                                                                }

                                                                return [$section->id => $label];
                                                            })
                                                            ->toArray();
                                                    })
                                                    ->required()
                                                    ->searchable()
                                                    ->preload()
                                                    ->live()
                                                    ->disabled(fn (callable $get) => !$get('grade_id'))
                                                    ->helperText(function (callable $get) {
                                                        $gradeId = $get('grade_id');
                                                        if (!$gradeId) {
                                                            return 'Please select a grade first';
                                                        }

                                                        $currentAcademicYear = AcademicYear::where('is_active', true)->first();
                                                        if (!$currentAcademicYear) {
                                                            return 'No active academic year found';
                                                        }

                                                        $sectionsCount = \App\Models\ClassSection::where('grade_id', $gradeId)
                                                            ->where('academic_year_id', $currentAcademicYear->id)
                                                            ->where('is_active', true)
                                                            ->count();

                                                        if ($sectionsCount === 0) {
                                                            return 'No class sections available for this grade. Please create sections first.';
                                                        }

                                                        return "Choose from {$sectionsCount} available section(s)";
                                                    })
                                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                        // Generate student ID when both grade and section are selected
                                                        if ($state && $get('grade_id')) {
                                                            $grade = Grade::find($get('grade_id'));
                                                            if ($grade) {
                                                                $studentId = self::generateStudentId($grade);
                                                                $set('student_id_number', $studentId);
                                                            }
                                                        }
                                                    }),

                                                Forms\Components\Placeholder::make('section_info')
                                                    ->label('Section Information')
                                                    ->content(function (callable $get) {
                                                        $sectionId = $get('class_section_id');
                                                        if (!$sectionId) {
                                                            return 'Select a class section to see details';
                                                        }

                                                        $section = \App\Models\ClassSection::with(['grade', 'classTeacher', 'academicYear'])
                                                            ->find($sectionId);

                                                        if (!$section) {
                                                            return 'Section not found';
                                                        }

                                                        $currentStudents = $section->students()->count();
                                                        $teacherName = $section->classTeacher?->name ?? 'Not assigned';

                                                        return "Section: {$section->grade->name} {$section->name}\n" .
                                                               "Academic Year: {$section->academicYear->name}\n" .
                                                               "Class Teacher: {$teacherName}\n" .
                                                               "Current Students: {$currentStudents}/{$section->capacity}";
                                                    })
                                                    ->visible(fn (callable $get) => !empty($get('class_section_id'))),

                                                Forms\Components\TextInput::make('student_id_number')
                                                    ->label('Student ID')
                                                    ->disabled()
                                                    ->dehydrated(true)
                                                    ->helperText('Generated automatically: YY + Grade Level + Sequential Number (e.g., 25300012)'),
                                            ]),

                                        Forms\Components\Card::make()
                                            ->schema([
                                                Forms\Components\Select::make('standard_of_education')
                                                    ->label('Level of Education')
                                                    ->options([
                                                        'Nursery' => 'Nursery',
                                                        'Primary' => 'Primary',
                                                        'Junior Secondary' => 'Junior Secondary',
                                                        'Senior Secondary' => 'Senior Secondary',
                                                    ])
                                                    ->helperText('Educational category the student belongs to'),

                                                Forms\Components\Select::make('enrollment_status')
                                                    ->options([
                                                        'active' => 'Active',
                                                        'inactive' => 'Inactive',
                                                        'graduated' => 'Graduated',
                                                        'transferred' => 'Transferred',
                                                    ])
                                                    ->default('active')
                                                    ->required(),
                                            ]),
                                    ])
                                    ->columns(2),

                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\DatePicker::make('admission_date')
                                            ->label('Date of Admission')
                                            ->required()
                                            ->default(now())
                                            ->displayFormat('d/m/Y'),

                                        Forms\Components\TextInput::make('previous_school')
                                            ->label('Previous School')
                                            ->maxLength(255)
                                            ->placeholder('Name of previous institution (if any)'),
                                    ])
                                    ->columns(2),
                            ]),

                        Forms\Components\Section::make('Medical Information')
                            ->description('Health-related information for school records')
                            ->icon('heroicon-o-heart')
                            ->schema([
                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\Select::make('smallpox_vaccination')
                                            ->label('Smallpox Vaccination')
                                            ->options([
                                                'Yes' => 'Yes',
                                                'No' => 'No',
                                                'Not Sure' => 'Not Sure',
                                            ])
                                            ->required()
                                            ->live(),

                                        Forms\Components\DatePicker::make('date_vaccinated')
                                            ->label('Date of Vaccination')
                                            ->displayFormat('d/m/Y')
                                            ->visible(fn (callable $get) => $get('smallpox_vaccination') === 'Yes'),
                                    ])
                                    ->columns(2),

                                Forms\Components\Textarea::make('medical_information')
                                    ->label('Other Medical Information')
                                    ->maxLength(65535)
                                    ->rows(3)
                                    ->placeholder('Include any allergies, medical conditions, or medications')
                                    ->helperText('Please include important health information the school should be aware of'),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Profile Image')
                            ->schema([
                                Forms\Components\FileUpload::make('profile_photo')
                                    ->label('Student Photo')
                                    ->image()
                                    ->directory('student-photos')
                                    ->maxSize(2048)
                                    ->imageCropAspectRatio('1:1')
                                    ->imageResizeTargetWidth('300')
                                    ->imageResizeTargetHeight('300'),
                            ]),

                        Forms\Components\Section::make('Parent/Guardian Information')
                            ->description('Student\'s parent or guardian details')
                            ->icon('heroicon-o-users')
                            ->schema([
                                Forms\Components\Select::make('parent_guardian_id')
                                    ->relationship('parentGuardian', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Full Name')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('email')
                                            ->label('Email Address')
                                            ->email()
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('phone')
                                            ->label('Phone Number')
                                            ->required()
                                            ->tel()
                                            ->placeholder('+260 XXX XXX XXX')
                                            ->maxLength(255),
                                        Forms\Components\Select::make('relationship')
                                            ->label('Relationship to Student')
                                            ->options([
                                                'father' => 'Father',
                                                'mother' => 'Mother',
                                                'guardian' => 'Guardian',
                                                'other' => 'Other',
                                            ])
                                            ->required(),
                                        Forms\Components\TextInput::make('address')
                                            ->label('Contact Address')
                                            ->required(),
                                    ])
                                    ->required(),
                            ]),

                        Forms\Components\Section::make('Additional Notes')
                            ->schema([
                                Forms\Components\Textarea::make('notes')
                                    ->maxLength(65535)
                                    ->rows(5)
                                    ->placeholder('Any additional information about the student')
                                    ->helperText('For internal use only'),
                            ])
                            ->collapsible(),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        $user = Auth::user();
        $isAdmin = $user->role_id === RoleConstants::ADMIN;
        $isTeacher = $user->role_id === RoleConstants::TEACHER;
        $isParent = $user->role_id === RoleConstants::PARENT;

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student_id_number')
                    ->label('Student ID')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->tooltip('Click to copy')
                    ->description(function (Student $record) {
                        if (!$record->student_id_number || strlen($record->student_id_number) < 8) {
                            return null;
                        }

                        $year = '20' . substr($record->student_id_number, 0, 2);
                        $gradeLevel = (int) substr($record->student_id_number, 2, 2);
                        $sequential = substr($record->student_id_number, 4);

                        return "Year: {$year}, Grade: {$gradeLevel}, #: {$sequential}";
                    }),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('grade.name')
                    ->label('Grade')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('classSection.name')
                    ->label('Section')
                    ->getStateUsing(function (Student $record) {
                        if (!$record->classSection) {
                            return 'Not Assigned';
                        }

                        $currentStudents = $record->classSection->students()->count();
                        $capacity = $record->classSection->capacity;

                        return "{$record->classSection->name} ({$currentStudents}/{$capacity})";
                    })
                    ->tooltip(function (Student $record) {
                        if (!$record->classSection) {
                            return 'Student needs to be assigned to a class section';
                        }

                        $section = $record->classSection;
                        $teacher = $section->classTeacher?->name ?? 'No teacher assigned';

                        return "Section: {$section->grade->name} {$section->name}\n" .
                               "Class Teacher: {$teacher}\n" .
                               "Academic Year: {$section->academicYear->name}";
                    })
                    ->badge()
                    ->color(function (Student $record) {
                        if (!$record->classSection) {
                            return 'danger'; // Red for unassigned
                        }

                        $currentStudents = $record->classSection->students()->count();
                        $capacity = $record->classSection->capacity;
                        $utilization = ($currentStudents / $capacity) * 100;

                        if ($utilization >= 95) return 'danger';
                        if ($utilization >= 80) return 'warning';
                        return 'success';
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('parentGuardian.name')
                    ->sortable()
                    ->label('Parent/Guardian')
                    ->visible($isTeacher || $isAdmin)
                    ->limit(20)
                    ->tooltip(function (Student $record) {
                        if (!$record->parentGuardian) return null;

                        return "Name: {$record->parentGuardian->name}\n" .
                               "Phone: {$record->parentGuardian->phone}\n" .
                               "Relationship: {$record->parentGuardian->relationship}";
                    }),

                Tables\Columns\TextColumn::make('gender')
                    ->searchable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'male' => 'blue',
                        'female' => 'pink',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('date_of_birth')
                    ->date('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

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
                    ->date('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('M j, Y H:i')
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

                Tables\Filters\SelectFilter::make('grade_id')
                    ->label('Grade')
                    ->relationship('grade', 'name')
                    ->visible($isTeacher || $isAdmin)
                    ->preload(),

                Tables\Filters\SelectFilter::make('class_section_id')
                    ->label('Class Section')
                    ->options(function () {
                        $currentAcademicYear = AcademicYear::where('is_active', true)->first();

                        if (!$currentAcademicYear) {
                            return [];
                        }

                        return \App\Models\ClassSection::with(['grade'])
                            ->where('academic_year_id', $currentAcademicYear->id)
                            ->where('is_active', true)
                            ->get()
                            ->mapWithKeys(function ($section) {
                                return [$section->id => "{$section->grade->name} {$section->name}"];
                            })
                            ->toArray();
                    })
                    ->visible($isTeacher || $isAdmin)
                    ->preload(),

                Tables\Filters\Filter::make('unassigned_to_section')
                    ->label('Unassigned to Section')
                    ->query(fn (Builder $query): Builder => $query->whereNull('class_section_id'))
                    ->visible($isAdmin)
                    ->toggle(),

                Tables\Filters\Filter::make('section_at_capacity')
                    ->label('In Full Sections')
                    ->query(function (Builder $query): Builder {
                        return $query->whereHas('classSection', function ($sectionQuery) {
                            $sectionQuery->whereRaw('(SELECT COUNT(*) FROM students WHERE students.class_section_id = class_sections.id) >= class_sections.capacity');
                        });
                    })
                    ->visible($isAdmin)
                    ->toggle(),

                Tables\Filters\SelectFilter::make('gender')
                    ->options([
                        'male' => 'Male',
                        'female' => 'Female',
                    ])
                    ->visible($isTeacher || $isAdmin),

                Tables\Filters\Filter::make('current_academic_year')
                    ->label('Current Academic Year Only')
                    ->query(function (Builder $query): Builder {
                        $currentAcademicYear = AcademicYear::where('is_active', true)->first();

                        if (!$currentAcademicYear) {
                            return $query;
                        }

                        return $query->whereHas('classSection', function ($sectionQuery) use ($currentAcademicYear) {
                            $sectionQuery->where('academic_year_id', $currentAcademicYear->id);
                        });
                    })
                    ->visible($isAdmin)
                    ->default(true)
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible($isAdmin),

                Tables\Actions\Action::make('assignToSection')
                    ->label('Assign to Section')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->color('warning')
                    ->visible(function (Student $record) use ($isAdmin) {
                        return $isAdmin && !$record->class_section_id;
                    })
                    ->form([
                        Forms\Components\Select::make('class_section_id')
                            ->label('Class Section')
                            ->options(function (Student $record) {
                                if (!$record->grade_id) {
                                    return [];
                                }

                                $currentAcademicYear = AcademicYear::where('is_active', true)->first();
                                if (!$currentAcademicYear) {
                                    return [];
                                }

                                return \App\Models\ClassSection::where('grade_id', $record->grade_id)
                                    ->where('academic_year_id', $currentAcademicYear->id)
                                    ->where('is_active', true)
                                    ->get()
                                    ->mapWithKeys(function ($section) {
                                        $currentStudents = $section->students()->count();
                                        $capacity = $section->capacity;
                                        $availableSpots = $capacity - $currentStudents;

                                        $label = "{$section->name} ({$currentStudents}/{$capacity})";
                                        if ($availableSpots <= 0) {
                                            $label .= " - FULL";
                                        } else if ($availableSpots <= 5) {
                                            $label .= " - {$availableSpots} spots left";
                                        }

                                        return [$section->id => $label];
                                    })
                                    ->toArray();
                            })
                            ->required()
                            ->searchable(),

                        Forms\Components\Placeholder::make('current_grade')
                            ->label('Current Grade')
                            ->content(fn (Student $record) => $record->grade?->name ?? 'No grade assigned'),

                        Forms\Components\Placeholder::make('student_info')
                            ->label('Student Information')
                            ->content(fn (Student $record) => "Name: {$record->name}\nStudent ID: {$record->student_id_number}"),
                    ])
                    ->action(function (Student $record, array $data) {
                        $section = \App\Models\ClassSection::find($data['class_section_id']);

                        if (!$section) {
                            Notification::make()
                                ->title('Section not found')
                                ->danger()
                                ->send();
                            return;
                        }

                        // Check capacity
                        $currentStudents = $section->students()->count();
                        if ($currentStudents >= $section->capacity) {
                            Notification::make()
                                ->title('Section is at capacity')
                                ->body("Section {$section->name} is full ({$currentStudents}/{$section->capacity})")
                                ->warning()
                                ->send();
                            return;
                        }

                        // Update student
                        $record->update([
                            'class_section_id' => $data['class_section_id']
                        ]);

                        // Generate new student ID
                        $grade = $record->grade;
                        if ($grade) {
                            $newStudentId = self::generateStudentId($grade);
                            $record->update(['student_id_number' => $newStudentId]);
                        }

                        Notification::make()
                            ->title('Student assigned to section')
                            ->body("Assigned {$record->name} to {$section->grade->name} {$section->name}")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('sendNotification')
                    ->label('Send SMS')
                    ->icon('heroicon-o-chat-bubble-left')
                    ->form([
                        Forms\Components\Textarea::make('message')
                            ->required()
                            ->default('Dear parent, this is an important message regarding your child.')
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
                                [$parentGuardian->name, $record->name, $record->grade?->name ?? 'Unknown'],
                                $data['message']
                            );

                            // Format phone and send message
                            $formattedPhone = self::formatPhoneNumber($parentGuardian->phone);

                            // Send the SMS with specific message type and reference
                            $sent = self::sendMessage(
                                $message,
                                $formattedPhone,
                                'student_notification',
                                $record->id
                            );

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
                    })
                    ->visible($isAdmin || $isTeacher),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible($isAdmin),

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
                        ->deselectRecordsAfterCompletion()
                        ->visible($isAdmin),

                    Tables\Actions\BulkAction::make('assignToSections')
                        ->label('Assign to Sections')
                        ->icon('heroicon-o-arrow-right-circle')
                        ->form([
                            Forms\Components\Select::make('assignment_method')
                                ->label('Assignment Method')
                                ->options([
                                    'same_section' => 'Assign all to the same section',
                                    'distribute_evenly' => 'Distribute evenly across available sections',
                                    'by_grade' => 'Auto-assign by grade (fill sections sequentially)',
                                ])
                                ->required()
                                ->live(),

                            Forms\Components\Select::make('specific_section_id')
                                ->label('Target Section')
                                ->options(function () {
                                    $currentAcademicYear = AcademicYear::where('is_active', true)->first();
                                    if (!$currentAcademicYear) {
                                        return [];
                                    }

                                    return \App\Models\ClassSection::with(['grade'])
                                        ->where('academic_year_id', $currentAcademicYear->id)
                                        ->where('is_active', true)
                                        ->get()
                                        ->mapWithKeys(function ($section) {
                                            $currentStudents = $section->students()->count();
                                            $capacity = $section->capacity;
                                            $availableSpots = $capacity - $currentStudents;

                                            $label = "{$section->grade->name} {$section->name} ({$currentStudents}/{$capacity})";
                                            if ($availableSpots <= 0) {
                                                $label .= " - FULL";
                                            } else {
                                                $label .= " - {$availableSpots} spots available";
                                            }

                                            return [$section->id => $label];
                                        })
                                        ->toArray();
                                })
                                ->required()
                                ->visible(fn (callable $get) => $get('assignment_method') === 'same_section'),

                            Forms\Components\Placeholder::make('warning')
                                ->label('Important')
                                ->content('This will update student IDs to match their enrollment year and grade. Make sure to inform students and parents of any ID changes.')
                                ->extraAttributes(['class' => 'text-amber-600']),
                        ])
                        ->action(function ($records, array $data): void {
                            $method = $data['assignment_method'];
                            $assignedCount = 0;
                            $failedCount = 0;
                            $errors = [];

                            foreach ($records as $student) {
                                // Skip if student already has a section
                                if ($student->class_section_id) {
                                    continue;
                                }

                                // Skip if student doesn't have a grade
                                if (!$student->grade_id) {
                                    $failedCount++;
                                    $errors[] = "{$student->name} - No grade assigned";
                                    continue;
                                }

                                try {
                                    $sectionId = null;

                                    switch ($method) {
                                        case 'same_section':
                                            $sectionId = $data['specific_section_id'];

                                            // Check if section belongs to student's grade
                                            $section = \App\Models\ClassSection::find($sectionId);
                                            if (!$section || $section->grade_id != $student->grade_id) {
                                                $failedCount++;
                                                $errors[] = "{$student->name} - Section doesn't match student's grade";
                                                continue 2;
                                            }
                                            break;

                                        case 'distribute_evenly':
                                        case 'by_grade':
                                            // Get available sections for this student's grade
                                            $currentAcademicYear = AcademicYear::where('is_active', true)->first();
                                            $availableSections = \App\Models\ClassSection::where('grade_id', $student->grade_id)
                                                ->where('academic_year_id', $currentAcademicYear->id)
                                                ->where('is_active', true)
                                                ->get()
                                                ->filter(function ($section) {
                                                    return $section->students()->count() < $section->capacity;
                                                });

                                            if ($availableSections->isEmpty()) {
                                                $failedCount++;
                                                $errors[] = "{$student->name} - No available sections in {$student->grade->name}";
                                                continue 2;
                                            }

                                            // For distribute_evenly, find section with least students
                                            // For by_grade, find first available section
                                            $targetSection = $availableSections->sortBy(function ($section) use ($method) {
                                                return $method === 'distribute_evenly'
                                                    ? $section->students()->count()
                                                    : $section->id;
                                            })->first();

                                            $sectionId = $targetSection->id;
                                            break;
                                    }

                                    if ($sectionId) {
                                        // Check final capacity
                                        $section = \App\Models\ClassSection::find($sectionId);
                                        $currentStudents = $section->students()->count();

                                        if ($currentStudents >= $section->capacity) {
                                            $failedCount++;
                                            $errors[] = "{$student->name} - Section {$section->name} is full";
                                            continue;
                                        }

                                        // Assign student to section
                                        $student->update(['class_section_id' => $sectionId]);

                                        // Generate new student ID
                                        $newStudentId = self::generateStudentId($student->grade);
                                        $student->update(['student_id_number' => $newStudentId]);

                                        $assignedCount++;
                                    }

                                } catch (\Exception $e) {
                                    $failedCount++;
                                    $errors[] = "{$student->name} - Error: {$e->getMessage()}";
                                    Log::error('Bulk section assignment error', [
                                        'student_id' => $student->id,
                                        'error' => $e->getMessage()
                                    ]);
                                }
                            }

                            // Show results notification
                            $title = "Bulk Assignment Results";
                            $body = "Successfully assigned: {$assignedCount} students";

                            if ($failedCount > 0) {
                                $body .= ", Failed: {$failedCount} students";

                                // Log detailed errors for admin review
                                if (!empty($errors)) {
                                    Log::warning('Bulk assignment failures', [
                                        'errors' => $errors,
                                        'user_id' => Auth::id()
                                    ]);

                                    $body .= ". Check logs for details.";
                                }
                            }

                            Notification::make()
                                ->title($title)
                                ->body($body)
                                ->success($assignedCount > 0)
                                ->warning($failedCount > 0)
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion()
                        ->visible($isAdmin),

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
                        ->action(function ($records, array $data): void {
                            $successCount = 0;
                            $failedCount = 0;

                            foreach ($records as $student) {
                                // Ensure we have the necessary relationships loaded
                                if (!$student->relationLoaded('parentGuardian')) {
                                    $student->load('parentGuardian');
                                }
                                if (!$student->relationLoaded('grade')) {
                                    $student->load('grade');
                                }

                                // Check if parent guardian exists and has phone
                                if (!$student->parentGuardian || !$student->parentGuardian->phone) {
                                    $failedCount++;
                                    continue;
                                }

                                try {
                                    // Personalize message with parent and student names
                                    $personalizedMessage = str_replace(
                                        ['{parent_name}', '{student_name}', '{grade}'],
                                        [
                                            $student->parentGuardian->name,
                                            $student->name,
                                            $student->grade?->name ?? 'Unknown'
                                        ],
                                        $data['message']
                                    );

                                    // Format phone and send
                                    $formattedPhone = self::formatPhoneNumber($student->parentGuardian->phone);

                                    // Send the SMS with specific message type and reference
                                    $sent = self::sendMessage(
                                        $personalizedMessage,
                                        $formattedPhone,
                                        'student_bulk_notification',
                                        $student->id
                                    );

                                    if ($sent) {
                                        $successCount++;
                                    } else {
                                        $failedCount++;
                                    }
                                } catch (\Exception $e) {
                                    $failedCount++;

                                    // Log error
                                    Log::error('Failed to send bulk SMS', [
                                        'student_id' => $student->id,
                                        'parent_guardian_id' => $student->parentGuardian?->id,
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
                        ->deselectRecordsAfterCompletion()
                        ->visible($isAdmin || $isTeacher),
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

    /**
     * Generate a student ID based on academic year + grade level + sequential number
     * Format: YYGGNNNN (e.g., 25030012 = Year 2025, Grade 3, Student #12)
     */
    public static function generateStudentId(Grade $grade): string
    {
        // Get current academic year
        $currentAcademicYear = AcademicYear::where('is_active', true)->first();

        if (!$currentAcademicYear) {
            // Fallback to current calendar year if no academic year is active
            $year = date('y'); // Last 2 digits of current year
        } else {
            // Extract year from academic year start date
            $year = $currentAcademicYear->start_date->format('y');
        }

        // Map grade names to grade levels (numbers)
        $gradeLevelMap = [
            'Baby Class' => '00',
            'Middle Class' => '01',
            'Reception' => '02',
            'Grade 1' => '03',
            'Grade 2' => '04',
            'Grade 3' => '05',
            'Grade 4' => '06',
            'Grade 5' => '07',
            'Grade 6' => '08',
            'Grade 7' => '09',
            'Grade 8' => '10',
            'Grade 9' => '11',
            'Grade 10' => '12',
            'Grade 11' => '13',
            'Grade 12' => '14',
        ];

        $gradeLevel = $gradeLevelMap[$grade->name] ?? '99'; // Default to 99 for unknown grades

        // Create the prefix: YY + GG
        $prefix = $year . $gradeLevel;

        // Find the latest student ID with this prefix
        $lastStudent = Student::where('student_id_number', 'like', $prefix . '%')
            ->orderBy('student_id_number', 'desc')
            ->first();

        if ($lastStudent && strlen($lastStudent->student_id_number) >= 8) {
            // Extract the sequential number from the last 4 digits
            $lastSequential = (int) substr($lastStudent->student_id_number, -4);
            $newSequential = $lastSequential + 1;
        } else {
            $newSequential = 1;
        }

        // Format the sequential number with leading zeros (4 digits)
        $sequentialFormatted = str_pad($newSequential, 4, '0', STR_PAD_LEFT);

        // Return the complete student ID: YYGGNNNN
        return $prefix . $sequentialFormatted;
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
     * Send a message via SMS and log it
     *
     * @param string $message_string The message content
     * @param string $phone_number The recipient's phone number
     * @param string $message_type The type of message (general, student_notification, etc.)
     * @param int|null $reference_id The ID of the related record (e.g., student ID)
     * @return bool Whether the message was sent successfully
     */
    public static function sendMessage($message_string, $phone_number, $message_type = 'general', $reference_id = null)
    {
        try {
            // Send the SMS
            $url_encoded_message = urlencode($message_string);
            $sendSenderSMS = Http::withoutVerifying()
                ->post('https://www.cloudservicezm.com/smsservice/httpapi?username=Blessmore&password=Blessmore&msg=' . $url_encoded_message . '&shortcode=2343&sender_id=StFrancis&phone=' . $phone_number . '&api_key=121231313213123123');

            // Map custom message types to allowed enum values
            $allowedMessageTypes = [
                'homework_notification',
                'result_notification',
                'fee_reminder',
                'event_notification',
                'general',
                'other'
            ];

            // Map your custom types to valid enum values
            $mappedMessageType = 'general'; // Default

            if ($message_type === 'student_notification' || $message_type === 'student_bulk_notification') {
                $mappedMessageType = 'general';
            } else if (in_array($message_type, $allowedMessageTypes)) {
                $mappedMessageType = $message_type;
            }

            // Calculate message cost
            $messageParts = ceil(strlen($message_string) / 160);
            $cost = 0.50 * $messageParts;

            // Create log entry
            DB::table('sms_logs')->insert([
                'recipient' => $phone_number,
                'message' => $message_string,
                'status' => $sendSenderSMS->successful() ? 'sent' : 'failed',
                'message_type' => $mappedMessageType,
                'reference_id' => $reference_id,
                'cost' => $cost,
                'provider_reference' => $sendSenderSMS->json('message_id') ?? null,
                'error_message' => $sendSenderSMS->successful() ? null : $sendSenderSMS->body(),
                'sent_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Log the response
            Log::info('SMS API Response', [
                'status' => $sendSenderSMS->status(),
                'body' => $sendSenderSMS->body(),
                'to' => substr($phone_number, 0, 6) . '****' . substr($phone_number, -3),
                'successful' => $sendSenderSMS->successful()
            ]);

            return $sendSenderSMS->successful();
        } catch (\Exception $e) {
            // Log the error
            Log::error('SMS sending failed', [
                'error' => $e->getMessage(),
                'phone' => $phone_number
            ]);

            // Try to log the failure
            try {
                DB::table('sms_logs')->insert([
                    'recipient' => $phone_number,
                    'message' => $message_string,
                    'status' => 'failed',
                    'message_type' => 'general', // Safe fallback
                    'reference_id' => $reference_id,
                    'cost' => ceil(strlen($message_string) / 160) * 0.50,
                    'error_message' => $e->getMessage(),
                    'sent_by' => Auth::id(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } catch (\Exception $logException) {
                Log::critical('Could not log SMS failure: ' . $logException->getMessage());
            }

            throw $e; // Re-throw to be caught by the calling method
        }
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        // Admin, Teachers, Nurses, and Parents can access
        if (in_array($user->role_id, [
            RoleConstants::ADMIN,
            RoleConstants::TEACHER,
            RoleConstants::NURSE,
            RoleConstants::PARENT
        ])) {
            return true;
        }

        return false;
    }

    public static function canCreate(): bool
    {
        // Only admin can create students
        return Auth::user()?->role_id === RoleConstants::ADMIN ?? false;
    }

    public static function canEditAny(): bool
    {
        // Only admin can edit students
        return Auth::user()?->role_id === RoleConstants::ADMIN ?? false;
    }

    public static function canDeleteAny(): bool
    {
        // Only admin can delete students
        return Auth::user()?->role_id === RoleConstants::ADMIN ?? false;
    }
}
