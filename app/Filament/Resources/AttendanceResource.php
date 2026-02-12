<?php

namespace App\Filament\Resources;

use App\Constants\RoleConstants;
use App\Filament\Resources\AttendanceResource\Pages;
use App\Filament\Resources\AttendanceResource\Widgets;
use App\Models\Attendance;
use App\Models\ClassSection;
use App\Models\Student;
use App\Models\Teacher;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Academic';

    protected static ?string $navigationLabel = 'Attendance';

    protected static ?int $navigationSort = 5;

    // Role-based query filtering
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        // Admin can see all attendance
        if ($user->role_id === RoleConstants::ADMIN) {
            return $query;
        }

        // Teachers can see attendance for their classes
        if ($user->role_id === RoleConstants::TEACHER) {
            $teacher = Teacher::where('user_id', $user->id)->first();

            if (! $teacher) {
                return $query->where('id', 0);
            }

            // Get class sections where this teacher is the grade teacher
            $classSectionIds = $teacher->classSections()->pluck('class_sections.id')->toArray();

            return $query->whereIn('class_section_id', $classSectionIds);
        }

        // Students can only see their own attendance
        if ($user->role_id === RoleConstants::STUDENT) {
            $student = Student::where('user_id', $user->id)->first();

            return $student ? $query->where('student_id', $student->id) : $query->where('id', 0);
        }

        // Parents can see attendance for their children
        if ($user->role_id === RoleConstants::PARENT) {
            $parent = $user->parentGuardian;
            $studentIds = $parent ? $parent->students()->pluck('id')->toArray() : [];

            return $query->whereIn('student_id', $studentIds);
        }

        return $query->where('id', 0); // Default: no access
    }

    public static function form(Form $form): Form
    {
        $user = Auth::user();
        $isTeacher = $user->role_id === RoleConstants::TEACHER;
        $isAdmin = $user->role_id === RoleConstants::ADMIN;
        $isStudent = $user->role_id === RoleConstants::STUDENT;

        $teacher = $isTeacher ? Teacher::where('user_id', $user->id)->first() : null;

        // Students and parents cannot create/edit attendance
        if ($isStudent || $user->role_id === RoleConstants::PARENT) {
            return $form->schema([
                Forms\Components\Placeholder::make('notice')
                    ->content('You can only view attendance records.'),
            ]);
        }

        // Get teacher's class sections
        $classSectionOptions = [];
        if ($teacher) {
            $classSectionOptions = $teacher->classSections()
                ->with('grade')
                ->get()
                ->mapWithKeys(function ($section) {
                    return [$section->id => $section->grade->name.' - '.$section->name];
                })
                ->toArray();
        } elseif ($isAdmin) {
            $classSectionOptions = ClassSection::with('grade')
                ->get()
                ->mapWithKeys(function ($section) {
                    return [$section->id => $section->grade->name.' - '.$section->name];
                })
                ->toArray();
        }

        return $form
            ->schema([
                Forms\Components\Section::make('Attendance Information')
                    ->schema([
                        Forms\Components\Select::make('class_section_id')
                            ->label('Class Section')
                            ->options($classSectionOptions)
                            ->required()
                            ->reactive()
                            ->searchable()
                            ->afterStateUpdated(function (callable $set) {
                                $set('student_id', null);
                            }),

                        Forms\Components\Select::make('student_id')
                            ->label('Student')
                            ->options(function (callable $get) {
                                $classSectionId = $get('class_section_id');
                                if (! $classSectionId) {
                                    return [];
                                }

                                return Student::where('class_section_id', $classSectionId)
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->required()
                            ->searchable()
                            ->reactive(),

                        Forms\Components\DatePicker::make('attendance_date')
                            ->label('Date')
                            ->required()
                            ->default(now())
                            ->maxDate(now())
                            ->displayFormat('d/m/Y'),

                        Forms\Components\Select::make('status')
                            ->options([
                                'present' => 'Present',
                                'absent' => 'Absent',
                                'sick' => 'Sick',
                                'late' => 'Late',
                                'excused' => 'Excused',
                            ])
                            ->required()
                            ->default('present')
                            ->reactive(),
                    ])->columns(2),

                Forms\Components\Section::make('Time Details')
                    ->schema([
                        Forms\Components\TimePicker::make('check_in_time')
                            ->label('Check In Time')
                            ->visible(fn (callable $get) => in_array($get('status'), ['present', 'late']))
                            ->seconds(false),

                        Forms\Components\TimePicker::make('check_out_time')
                            ->label('Check Out Time')
                            ->visible(fn (callable $get) => $get('status') === 'present')
                            ->seconds(false),
                    ])->columns(2),

                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->maxLength(500)
                            ->columnSpanFull(),

                        Forms\Components\Hidden::make('marked_by')
                            ->default($user->id),

                        Forms\Components\Hidden::make('grade_id')
                            ->default(function (callable $get) {
                                $classSectionId = $get('class_section_id');
                                if ($classSectionId) {
                                    $classSection = ClassSection::find($classSectionId);

                                    return $classSection?->grade_id;
                                }

                                return null;
                            }),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        $user = Auth::user();
        $canEdit = in_array($user->role_id, [RoleConstants::ADMIN, RoleConstants::TEACHER]);
        $isStudent = $user->role_id === RoleConstants::STUDENT;

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('attendance_date')
                    ->label('Date')
                    ->date('D, d M Y')
                    ->sortable()
                    ->searchable()
                    ->weight('bold')
                    ->size('sm'),

                Tables\Columns\TextColumn::make('student.name')
                    ->label('Student')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn ($record) => 'ID: '.$record->student->id)
                    ->hidden($isStudent),

                Tables\Columns\TextColumn::make('classSection.name')
                    ->label('Class')
                    ->formatStateUsing(fn ($record) => $record->grade->name.' - '.$record->classSection->name)
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->hidden($isStudent),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'present',
                        'danger' => 'absent',
                        'info' => 'sick',
                        'warning' => 'late',
                        'purple' => 'excused',
                    ])
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->size('lg')
                    ->icons([
                        'heroicon-o-check-circle' => 'present',
                        'heroicon-o-x-circle' => 'absent',
                        'heroicon-o-heart' => 'sick',
                        'heroicon-o-clock' => 'late',
                        'heroicon-o-shield-check' => 'excused',
                    ]),

                Tables\Columns\TextColumn::make('check_in_time')
                    ->label('Check In')
                    ->time('H:i')
                    ->placeholder('-')
                    ->description('Time'),

                Tables\Columns\TextColumn::make('check_out_time')
                    ->label('Check Out')
                    ->time('H:i')
                    ->placeholder('-')
                    ->toggleable()
                    ->description('Time'),

                Tables\Columns\TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(40)
                    ->placeholder('No notes')
                    ->wrap()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('markedBy.name')
                    ->label('Marked By')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->hidden($isStudent)
                    ->description(fn ($record) => $record->updated_at->diffForHumans()),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('class_section')
                    ->relationship('classSection', 'name')
                    ->label('Class Section'),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'present' => 'Present',
                        'absent' => 'Absent',
                        'sick' => 'Sick',
                        'late' => 'Late',
                        'excused' => 'Excused',
                    ]),

                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('From Date'),
                        Forms\Components\DatePicker::make('to')
                            ->label('To Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('attendance_date', '>=', $date),
                            )
                            ->when(
                                $data['to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('attendance_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible($canEdit),
                Tables\Actions\DeleteAction::make()
                    ->visible($canEdit),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('mark_present')
                        ->label('Mark as Present')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update([
                                    'status' => 'present',
                                    'check_in_time' => now(),
                                ]);
                            }

                            Notification::make()
                                ->title('Marked as Present')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion()
                        ->visible($canEdit),

                    Tables\Actions\BulkAction::make('mark_absent')
                        ->label('Mark as Absent')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update([
                                    'status' => 'absent',
                                    'check_in_time' => null,
                                    'check_out_time' => null,
                                ]);
                            }

                            Notification::make()
                                ->title('Marked as Absent')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion()
                        ->visible($canEdit),

                    Tables\Actions\BulkAction::make('mark_late')
                        ->label('Mark as Late')
                        ->icon('heroicon-o-clock')
                        ->color('warning')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update([
                                    'status' => 'late',
                                    'check_in_time' => now(),
                                ]);
                            }

                            Notification::make()
                                ->title('Marked as Late')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion()
                        ->visible($canEdit),

                    Tables\Actions\DeleteBulkAction::make()
                        ->visible($canEdit),
                ])
                    ->visible($canEdit),
            ])
            ->defaultSort('attendance_date', 'desc');
    }

    public static function getWidgets(): array
    {
        return [
            Widgets\AttendanceDailyRegister::class,
            Widgets\FlaggedStudentsWidget::class,
        ];
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttendances::route('/'),
            'create' => Pages\CreateAttendance::route('/create'),
            'view' => Pages\ViewAttendance::route('/{record}'),
            'edit' => Pages\EditAttendance::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        return in_array($user->role_id, [
            RoleConstants::ADMIN,
            RoleConstants::TEACHER,
            RoleConstants::STUDENT,
            RoleConstants::PARENT,
        ]);
    }

    public static function canCreate(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        return in_array($user->role_id, [RoleConstants::ADMIN, RoleConstants::TEACHER]);
    }

    public static function canEdit($record): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        return in_array($user->role_id, [RoleConstants::ADMIN, RoleConstants::TEACHER]);
    }

    public static function canDelete($record): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        return $user->role_id === RoleConstants::ADMIN;
    }

    public static function canDeleteAny(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        return $user->role_id === RoleConstants::ADMIN;
    }
}
