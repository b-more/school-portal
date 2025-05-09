<?php

namespace App\Filament\Pages;

use App\Models\AcademicYear;
use App\Models\SchoolSection;
use App\Models\SchoolSettings;
use App\Models\SystemConfiguration;
use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Forms\Components;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class SetupWizard extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-rocket-launch';
    protected static ?string $navigationLabel = 'Setup Wizard';
    protected static ?string $navigationGroup = 'System Configuration';
    protected static ?int $navigationSort = 0;
    protected static string $view = 'filament.pages.setup-wizard';

    public $currentStep = 1;
    public $totalSteps = 4;

    // Form data for each step
    public $schoolData = [];
    public $academicYearData = [];
    public $sectionsData = [];
    public $termsData = [];

    public function mount(): void
    {
        // Check if initial setup has been completed
        if ($this->isSetupComplete()) {
            // Redirect to dashboard with a message
            redirect()->route('filament.admin.pages.dashboard')->with('message', 'System setup has already been completed.');
            return;
        }

        // Initialize form states instead of calling fill() directly
        $this->schoolData = [];
        $this->academicYearData = [];
        $this->sectionsData = [];
        $this->termsData = [];
    }

    // Define form state paths for each form
    protected function getSchoolFormStatePath(): string
    {
        return 'schoolData';
    }

    protected function getAcademicYearFormStatePath(): string
    {
        return 'academicYearData';
    }

    protected function getSectionsFormStatePath(): string
    {
        return 'sectionsData';
    }

    protected function getTermsFormStatePath(): string
    {
        return 'termsData';
    }

    // School information form
    public function schoolForm(Form $form): Form
    {
        return $form
            ->schema([
                Components\Section::make('School Information')
                    ->description('Enter your school\'s basic information')
                    ->schema([
                        Components\TextInput::make('school_name')
                            ->label('School Name')
                            ->required()
                            ->maxLength(255),

                        Components\TextInput::make('school_code')
                            ->label('School Code')
                            ->maxLength(50),

                        Components\TextInput::make('school_motto')
                            ->label('School Motto')
                            ->maxLength(255),

                        Components\FileUpload::make('school_logo')
                            ->label('School Logo')
                            ->image()
                            ->directory('school-logos')
                            ->visibility('public')
                            ->maxSize(2048),

                        Components\Textarea::make('address')
                            ->label('Address')
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Components\Grid::make()
                            ->schema([
                                Components\TextInput::make('city')
                                    ->maxLength(100),

                                Components\TextInput::make('state_province')
                                    ->maxLength(100),

                                Components\TextInput::make('country')
                                    ->maxLength(100)
                                    ->default('Zambia'),

                                Components\TextInput::make('postal_code')
                                    ->maxLength(20),
                            ])
                            ->columns(2),

                        Components\Grid::make()
                            ->schema([
                                Components\TextInput::make('phone')
                                    ->tel()
                                    ->maxLength(20),

                                Components\TextInput::make('email')
                                    ->email()
                                    ->maxLength(255),

                                Components\TextInput::make('website')
                                    ->url()
                                    ->maxLength(255),
                            ])
                            ->columns(3),
                    ]),
            ])
            ->statePath('schoolData');
    }

    // Academic year form
    public function academicYearForm(Form $form): Form
    {
        return $form
            ->schema([
                Components\Section::make('Academic Year')
                    ->description('Configure the first academic year for your school')
                    ->schema([
                        Components\TextInput::make('name')
                            ->label('Academic Year Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g. 2025-2026'),

                        Components\DatePicker::make('start_date')
                            ->label('Start Date')
                            ->required()
                            ->default(now()->startOfYear()),

                        Components\DatePicker::make('end_date')
                            ->label('End Date')
                            ->required()
                            ->default(now()->endOfYear())
                            ->after('start_date'),

                        Components\Select::make('number_of_terms')
                            ->label('Number of Terms')
                            ->options([
                                1 => '1 Term',
                                2 => '2 Terms',
                                3 => '3 Terms',
                                4 => '4 Terms',
                            ])
                            ->default(3)
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn (callable $set) => $set('terms', [])),

                        Components\Textarea::make('description')
                            ->label('Description')
                            ->maxLength(65535),

                        Components\Toggle::make('is_active')
                            ->label('Set as active academic year')
                            ->helperText('The first academic year will be automatically set as active')
                            ->default(true)
                            ->disabled(),
                    ]),
            ])
            ->statePath('academicYearData');
    }

    // School sections form
    public function sectionsForm(Form $form): Form
    {
        return $form
            ->schema([
                Components\Section::make('School Sections')
                    ->description('Define the different sections of your school')
                    ->schema([
                        Components\Repeater::make('sections')
                            ->schema([
                                Components\TextInput::make('name')
                                    ->label('Section Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('e.g. Early Learning Center'),

                                Components\TextInput::make('code')
                                    ->label('Section Code')
                                    ->required()
                                    ->maxLength(10)
                                    ->placeholder('e.g. ELC'),

                                Components\Textarea::make('description')
                                    ->label('Description')
                                    ->maxLength(65535),

                                Components\TextInput::make('order')
                                    ->label('Display Order')
                                    ->numeric()
                                    ->default(0),
                            ])
                            ->columns(2)
                            ->defaultItems(4)
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                            ->reorderable()
                            ->reorderableWithButtons(),
                    ]),
            ])
            ->statePath('sectionsData');
    }

    // Terms form
    public function termsForm(Form $form): Form
    {
        return $form
            ->schema([
                Components\Section::make('Terms Configuration')
                    ->description('Configure the terms for your academic year')
                    ->schema([
                        Components\Placeholder::make('academic_year_info')
                            ->label('Academic Year')
                            ->content(fn () => $this->academicYearData['name'] ?? 'Not set'),

                        Components\Repeater::make('terms')
                            ->schema([
                                Components\TextInput::make('name')
                                    ->label('Term Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->default(fn ($state, $context) => 'Term ' . ($context + 1)),

                                Components\DatePicker::make('start_date')
                                    ->label('Start Date')
                                    ->required(),

                                Components\DatePicker::make('end_date')
                                    ->label('End Date')
                                    ->required()
                                    ->after('start_date'),

                                Components\Textarea::make('description')
                                    ->label('Description')
                                    ->maxLength(65535),

                                Components\Toggle::make('is_active')
                                    ->label('Set as active term')
                                    ->default(fn ($state, $context) => $context === 0)
                                    ->reactive(),
                            ])
                            ->columns(2)
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                            ->minItems(fn () => (int)($this->academicYearData['number_of_terms'] ?? 3))
                            ->maxItems(fn () => (int)($this->academicYearData['number_of_terms'] ?? 3))
                            ->defaultItems(fn () => (int)($this->academicYearData['number_of_terms'] ?? 3)),
                    ]),
            ])
            ->statePath('termsData');
    }

    // Check if initial setup has been completed
    private function isSetupComplete(): bool
    {
        // Check if we have at least one academic year and school settings
        $academicYearCount = AcademicYear::count();
        $schoolSettings = SchoolSettings::first();

        return $academicYearCount > 0 && $schoolSettings !== null;
    }

    // Step navigation
    public function nextStep(): void
    {
        // Validate the current step using form state instead of accessing form directly
        if ($this->currentStep === 1) {
            $data = $this->schoolForm->getState();
        } elseif ($this->currentStep === 2) {
            $data = $this->academicYearForm->getState();
        } elseif ($this->currentStep === 3) {
            $data = $this->sectionsForm->getState();
        }

        // Calculate term dates if moving to terms step
        if ($this->currentStep === 2) {
            $this->calculateTermDates();
        }

        // Increment step if not at the end
        if ($this->currentStep < $this->totalSteps) {
            $this->currentStep++;
        }
    }

    public function previousStep(): void
    {
        // Decrement step if not at the beginning
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    // Calculate term dates based on academic year
    private function calculateTermDates(): void
    {
        if (empty($this->academicYearData['start_date']) ||
            empty($this->academicYearData['end_date']) ||
            empty($this->academicYearData['number_of_terms'])) {
            return;
        }

        $startDate = \Carbon\Carbon::parse($this->academicYearData['start_date']);
        $endDate = \Carbon\Carbon::parse($this->academicYearData['end_date']);
        $termCount = (int)$this->academicYearData['number_of_terms'];

        // Calculate date ranges for terms
        $interval = $startDate->diffInDays($endDate) / $termCount;

        $terms = [];
        for ($i = 1; $i <= $termCount; $i++) {
            $termStartDate = $startDate->copy()->addDays(($i-1) * $interval);
            $termEndDate = $startDate->copy()->addDays($i * $interval)->subDay();

            if ($i === $termCount) {
                $termEndDate = $endDate; // Make sure the last term ends on the academic year end date
            }

            $terms[] = [
                'name' => 'Term ' . $i,
                'start_date' => $termStartDate->format('Y-m-d'),
                'end_date' => $termEndDate->format('Y-m-d'),
                'description' => '',
                'is_active' => ($i === 1), // First term is active by default
            ];
        }

        $this->termsData['terms'] = $terms;
    }

    // Complete setup and save all data
    public function completeSetup(): void
    {
        // Get form state instead of validating directly
        $termsData = $this->termsForm->getState();

        // Begin transaction to ensure all or nothing
        DB::beginTransaction();

        try {
            // 1. Save school settings
            $schoolSettings = SchoolSettings::updateOrCreate(
                ['id' => 1],
                $this->schoolData
            );

            // 2. Create academic year
            $academicYear = AcademicYear::create([
                'name' => $this->academicYearData['name'],
                'start_date' => $this->academicYearData['start_date'],
                'end_date' => $this->academicYearData['end_date'],
                'description' => $this->academicYearData['description'] ?? null,
                'is_active' => true,
                'number_of_terms' => $this->academicYearData['number_of_terms'],
            ]);

            // 3. Create school sections
            foreach ($this->sectionsData['sections'] as $index => $sectionData) {
                SchoolSection::create([
                    'name' => $sectionData['name'],
                    'code' => $sectionData['code'],
                    'description' => $sectionData['description'] ?? null,
                    'order' => $sectionData['order'] ?? $index,
                    'is_active' => true,
                ]);
            }

            // 4. Create terms
            foreach ($this->termsData['terms'] as $termData) {
                $academicYear->terms()->create([
                    'name' => $termData['name'],
                    'start_date' => $termData['start_date'],
                    'end_date' => $termData['end_date'],
                    'description' => $termData['description'] ?? null,
                    'is_active' => $termData['is_active'] ?? false,
                ]);
            }

            // 5. Create system configuration flag indicating setup is complete
            SystemConfiguration::updateOrCreate(
                ['key' => 'system.setup_completed'],
                [
                    'value' => 'true',
                    'group' => 'general',
                    'description' => 'Indicates that initial system setup has been completed',
                    'type' => 'boolean',
                    'is_public' => true,
                ]
            );

            DB::commit();

            Notification::make()
                ->title('Setup completed successfully')
                ->body('Your school system has been configured and is ready to use.')
                ->success()
                ->send();

            // Redirect to dashboard
            redirect()->route('filament.admin.pages.dashboard');

        } catch (\Exception $e) {
            DB::rollBack();

            Notification::make()
                ->title('Setup failed')
                ->body('An error occurred during setup: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    // Get view data for the template
    protected function getViewData(): array
    {
        return [
            'currentStep' => $this->currentStep,
            'totalSteps' => $this->totalSteps,
        ];
    }
}
