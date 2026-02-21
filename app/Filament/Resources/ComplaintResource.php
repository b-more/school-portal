<?php

namespace App\Filament\Resources;

use App\Constants\RoleConstants;
use App\Filament\Resources\ComplaintResource\Pages;
use App\Models\Complaint;
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ComplaintResource extends Resource
{
    protected static ?string $model = Complaint::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $navigationGroup = 'Secretary Desk';

    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return in_array(auth()->user()?->role_id, [RoleConstants::ADMIN, RoleConstants::SCHOOL_SECRETARY]);
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Complaint::where('status', 'open')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'danger';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Complainant Information')
                    ->schema([
                        Forms\Components\TextInput::make('complainant_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->required()
                            ->maxLength(20),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\Select::make('related_student_id')
                            ->label('Related Student')
                            ->options(Student::orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->preload(),
                    ])->columns(2),

                Forms\Components\Section::make('Complaint Details')
                    ->schema([
                        Forms\Components\Select::make('complaint_type')
                            ->options([
                                'academic' => 'Academic',
                                'behavioral' => 'Behavioral',
                                'facility' => 'Facility',
                                'staff' => 'Staff',
                                'other' => 'Other',
                            ])
                            ->required(),
                        Forms\Components\Select::make('priority')
                            ->options([
                                'low' => 'Low',
                                'medium' => 'Medium',
                                'high' => 'High',
                                'urgent' => 'Urgent',
                            ])
                            ->default('medium')
                            ->required(),
                        Forms\Components\TextInput::make('subject')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Resolution')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'open' => 'Open',
                                'in_progress' => 'In Progress',
                                'resolved' => 'Resolved',
                                'closed' => 'Closed',
                            ])
                            ->default('open')
                            ->required()
                            ->live(),
                        Forms\Components\Textarea::make('resolution')
                            ->rows(3)
                            ->visible(fn (Forms\Get $get) => in_array($get('status'), ['resolved', 'closed']))
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                Tables\Columns\TextColumn::make('complainant_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subject')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\BadgeColumn::make('complaint_type')
                    ->colors([
                        'info' => 'academic',
                        'warning' => 'behavioral',
                        'gray' => 'facility',
                        'danger' => 'staff',
                        'secondary' => 'other',
                    ]),
                Tables\Columns\BadgeColumn::make('priority')
                    ->colors([
                        'gray' => 'low',
                        'info' => 'medium',
                        'warning' => 'high',
                        'danger' => 'urgent',
                    ]),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'danger' => 'open',
                        'warning' => 'in_progress',
                        'success' => 'resolved',
                        'gray' => 'closed',
                    ]),
                Tables\Columns\TextColumn::make('logger.name')
                    ->label('Logged By')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'open' => 'Open',
                        'in_progress' => 'In Progress',
                        'resolved' => 'Resolved',
                        'closed' => 'Closed',
                    ]),
                Tables\Filters\SelectFilter::make('complaint_type')
                    ->options([
                        'academic' => 'Academic',
                        'behavioral' => 'Behavioral',
                        'facility' => 'Facility',
                        'staff' => 'Staff',
                        'other' => 'Other',
                    ]),
                Tables\Filters\SelectFilter::make('priority')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                        'urgent' => 'Urgent',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListComplaints::route('/'),
            'create' => Pages\CreateComplaint::route('/create'),
            'view' => Pages\ViewComplaint::route('/{record}'),
            'edit' => Pages\EditComplaint::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['logger:id,name', 'resolver:id,name', 'student:id,name']);
    }
}
