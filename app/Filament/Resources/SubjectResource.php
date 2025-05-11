<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubjectResource\Pages;
use App\Filament\Resources\SubjectResource\RelationManagers;
use App\Models\Subject;
use App\Models\AcademicYear;
use App\Models\Grade;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use App\Constants\RoleConstants;
use Illuminate\Support\Facades\Auth;


class SubjectResource extends Resource
{
    protected static ?string $model = Subject::class;
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationGroup = 'Academic Configuration';
    protected static ?int $navigationSort = 5;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role_id === RoleConstants::ADMIN ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Subject Details')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('code')
                                    ->required()
                                    ->maxLength(20)
                                    ->unique(ignoreRecord: true),

                                Forms\Components\Select::make('academic_year_id')
                                    ->label('Academic Year')
                                    ->options(AcademicYear::orderByDesc('is_active')->orderByDesc('start_date')->pluck('name', 'id'))
                                    ->default(fn () => AcademicYear::where('is_active', true)->first()?->id)
                                    ->searchable(),

                                Forms\Components\Select::make('grade_level')
                                    ->options([
                                        'Primary' => 'Primary',
                                        'Secondary' => 'Secondary',
                                        'All' => 'All Levels',
                                    ])
                                    ->required(),

                                Forms\Components\Textarea::make('description')
                                    ->maxLength(65535)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),

                        Forms\Components\Section::make('Subject Configuration')
                            ->schema([
                                Forms\Components\Toggle::make('is_core')
                                    ->label('Core Subject')
                                    ->helperText('Core subjects are required for progression')
                                    ->default(true),

                                Forms\Components\Toggle::make('is_active')
                                    ->required()
                                    ->default(true),

                                Forms\Components\TextInput::make('credit_hours')
                                    ->numeric()
                                    ->label('Credit Hours')
                                    ->default(1)
                                    ->helperText('Number of credit hours for this subject'),

                                Forms\Components\TextInput::make('weight')
                                    ->numeric()
                                    ->label('Weight')
                                    ->default(1.0)
                                    ->step(0.1)
                                    ->helperText('Weight for calculating grade point average'),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpanFull(),

                Forms\Components\Section::make('Assign to Grades')
                    ->visible(fn (string $operation): bool => $operation !== 'create')
                    ->schema([
                        Forms\Components\CheckboxList::make('grades')
                            ->relationship('grades', 'name')
                            ->options(function () {
                                return Grade::where('is_active', true)->get()->pluck('name', 'id');
                            })
                            ->columns(3)
                            ->bulkToggleable()
                            ->helperText('Select which grades this subject should be assigned to'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('academicYear.name')
                    ->label('Academic Year')
                    ->sortable(),

                Tables\Columns\TextColumn::make('grade_level')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_core')
                    ->boolean()
                    ->label('Core'),

                Tables\Columns\TextColumn::make('grades_count')
                    ->counts('grades')
                    ->label('Assigned Grades'),

                Tables\Columns\TextColumn::make('credit_hours')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('grade_level')
                    ->options([
                        'Primary' => 'Primary',
                        'Secondary' => 'Secondary',
                        'All' => 'All Levels',
                    ]),

                Tables\Filters\SelectFilter::make('academic_year')
                    ->relationship('academicYear', 'name'),

                Tables\Filters\Filter::make('is_active')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true))
                    ->label('Active only')
                    ->toggle(),

                Tables\Filters\Filter::make('is_core')
                    ->query(fn (Builder $query): Builder => $query->where('is_core', true))
                    ->label('Core subjects only')
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('assignToGrades')
                    ->label('Assign to Grades')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->color('success')
                    ->form([
                        Forms\Components\CheckboxList::make('grade_ids')
                            ->label('Select Grades')
                            ->options(function () {
                                return Grade::where('is_active', true)->get()->pluck('name', 'id');
                            })
                            ->required()
                            ->columns(3)
                            ->bulkToggleable(),

                        Forms\Components\Toggle::make('is_mandatory')
                            ->label('Mandatory Subject')
                            ->helperText('Is this subject mandatory for selected grades?')
                            ->default(true),
                    ])
                    ->action(function (Subject $record, array $data) {
                        $gradeIds = $data['grade_ids'];
                        $isMandatory = $data['is_mandatory'];

                        // Sync with specified grades
                        foreach ($gradeIds as $gradeId) {
                            $record->grades()->syncWithoutDetaching([
                                $gradeId => ['is_mandatory' => $isMandatory]
                            ]);
                        }

                        Notification::make()
                            ->title('Subject assigned to grades')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('bulkAssignToGrades')
                        ->label('Assign to Grades')
                        ->icon('heroicon-o-clipboard-document-list')
                        ->color('success')
                        ->form([
                            Forms\Components\CheckboxList::make('grade_ids')
                                ->label('Select Grades')
                                ->options(function () {
                                    return Grade::where('is_active', true)->get()->pluck('name', 'id');
                                })
                                ->required()
                                ->columns(3)
                                ->bulkToggleable(),

                            Forms\Components\Toggle::make('is_mandatory')
                                ->label('Mandatory Subject')
                                ->helperText('Is this subject mandatory for selected grades?')
                                ->default(true),
                        ])
                        ->action(function ($records, array $data) {
                            $gradeIds = $data['grade_ids'];
                            $isMandatory = $data['is_mandatory'];

                            foreach ($records as $subject) {
                                // Sync with specified grades
                                foreach ($gradeIds as $gradeId) {
                                    $subject->grades()->syncWithoutDetaching([
                                        $gradeId => ['is_mandatory' => $isMandatory]
                                    ]);
                                }
                            }

                            Notification::make()
                                ->title('Subjects assigned to grades')
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // RelationManagers\GradesRelationManager::class,
            // RelationManagers\EmployeesRelationManager::class,
            // RelationManagers\HomeworksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubjects::route('/'),
            'create' => Pages\CreateSubject::route('/create'),
            //'view' => Pages\ViewSubject::route('/{record}'),
            'edit' => Pages\EditSubject::route('/{record}/edit'),
        ];
    }
}
