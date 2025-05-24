<?php

namespace App\Filament\Resources\CommunicationCenterResource\Pages;

use App\Filament\Resources\CommunicationCenterResource;
use App\Models\Grade;
use App\Models\MessageBroadcast;
use App\Models\MessageTemplate;
use App\Models\ParentGuardian;
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;

class CreateBroadcast extends CreateRecord
{
    protected static string $resource = CommunicationCenterResource::class;

    public function getFormStatePath(): string
    {
        return 'data';
    }

    protected function createRecord(): MessageBroadcast
    {
        return new MessageBroadcast();
    }

    protected function beforeCreate(): void
    {
        // Override to prevent automatic record creation
    }

    // Initialize properties
    public ?array $data = [];
    public array $recipients = [];
    public array $recipientPreview = [];
    public int $recipientCount = 0;
    public float $estimatedCost = 0;

    public function create(bool $another = false): void
    {
        $this->create_broadcast();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Select Recipients')
                        ->schema([
                            Forms\Components\Select::make('recipient_type')
                                ->label('Recipient Type')
                                ->options([
                                    'parents' => 'Parents/Guardians',
                                    'staff' => 'Staff Members',
                                    'teachers' => 'Teachers',
                                ])
                                ->default('parents')
                                ->required()
                                ->live(),

                            Forms\Components\Section::make('Filter Options')
                                ->schema([
                                    Forms\Components\Grid::make()
                                        ->schema([
                                            Forms\Components\Select::make('grade_id')
                                                ->label('Grade')
                                                ->options(function () {
                                                    return Grade::query()
                                                        ->where('is_active', true)
                                                        ->orderBy('level')
                                                        ->pluck('name', 'id')
                                                        ->toArray();
                                                })
                                                ->placeholder('All Grades')
                                                ->live(),

                                            Forms\Components\Select::make('fee_status')
                                                ->label('Fee Status')
                                                ->options([
                                                    'all' => 'All',
                                                    'paid' => 'Fully Paid',
                                                    'partial' => 'Partially Paid',
                                                    'unpaid' => 'Unpaid',
                                                ])
                                                ->default('all')
                                                ->live(),
                                        ])
                                        ->visible(fn (Get $get): bool => $get('recipient_type') === 'parents')
                                        ->columns(2),

                                    Forms\Components\Select::make('department')
                                        ->label('Department')
                                        ->options([
                                            'all' => 'All Departments',
                                            'academic' => 'Academic',
                                            'administration' => 'Administration',
                                            'support' => 'Support Staff',
                                        ])
                                        ->default('all')
                                        ->visible(fn (Get $get): bool => $get('recipient_type') === 'staff')
                                        ->live(),

                                    Forms\Components\Select::make('subject')
                                        ->label('Subject')
                                        ->options(function () {
                                            return [
                                                'all' => 'All Subjects',
                                                'mathematics' => 'Mathematics',
                                                'english' => 'English',
                                                'science' => 'Science',
                                            ];
                                        })
                                        ->default('all')
                                        ->visible(fn (Get $get): bool => $get('recipient_type') === 'teachers')
                                        ->live(),
                                ]),

                            Forms\Components\Actions::make([
                                Forms\Components\Actions\Action::make('preview_recipients')
                                    ->label('Preview Recipients')
                                    ->action('previewRecipients')
                                    ->color('primary')
                                    ->requiresConfirmation(false),
                            ]),

                            Forms\Components\Placeholder::make('recipient_count')
                                ->label('Messages to Send')
                                ->content(function (Get $get) {
                                    if ($this->recipientCount > 0) {
                                        return new HtmlString(
                                            "<div class='text-lg font-bold'>{$this->recipientCount} messages will be sent</div>" .
                                            "<div class='text-sm text-gray-500'>Estimated cost: ZMW {$this->estimatedCost}</div>" .
                                            "<div class='text-xs text-blue-600 mt-2'>Note: Parents with multiple children will receive separate SMS for each child</div>"
                                        );
                                    }
                                    return 'No recipients selected yet';
                                }),

                            // Improved preview display
                            Forms\Components\Section::make('Selected Recipients')
                                ->schema([
                                    Forms\Components\Placeholder::make('recipients_summary')
                                        ->label('')
                                        ->content(function () {
                                            if ($this->recipientCount === 0) {
                                                return new HtmlString('<div class="text-gray-500 text-center py-4">No recipients selected yet. Use the "Preview Recipients" button above.</div>');
                                            }

                                            $html = '<div class="space-y-4">';

                                            // Summary card
                                            $html .= '<div class="bg-blue-50 border border-blue-200 rounded-lg p-4">';
                                            $html .= '<div class="flex justify-between items-center">';
                                            $html .= '<div>';
                                            $html .= '<h3 class="text-lg font-semibold text-blue-800">' . $this->recipientCount . ' Messages Selected</h3>';
                                            $html .= '<p class="text-blue-600">Estimated Cost: ZMW ' . number_format($this->estimatedCost, 2) . '</p>';
                                            $html .= '</div>';
                                            $html .= '<div class="text-right">';
                                            $html .= '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">';
                                            $html .= 'Ready to Send';
                                            $html .= '</span>';
                                            $html .= '</div>';
                                            $html .= '</div>';
                                            $html .= '</div>';

                                            // Recipients list
                                            if (!empty($this->recipientPreview)) {
                                                $html .= '<div class="bg-white border border-gray-200 rounded-lg overflow-hidden">';
                                                $html .= '<div class="bg-gray-50 px-4 py-2 border-b border-gray-200">';
                                                $html .= '<h4 class="font-medium text-gray-900">First 10 Messages Preview</h4>';
                                                $html .= '</div>';
                                                $html .= '<div class="divide-y divide-gray-200">';

                                                foreach (array_slice($this->recipientPreview, 0, 10) as $index => $recipient) {
                                                    $html .= '<div class="px-4 py-3 hover:bg-gray-50">';
                                                    $html .= '<div class="flex items-center justify-between">';
                                                    $html .= '<div class="flex-1">';
                                                    $html .= '<div class="flex items-center space-x-3">';
                                                    $html .= '<div class="flex-shrink-0">';
                                                    $html .= '<div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">';
                                                    $html .= '<span class="text-sm font-medium text-blue-600">' . ($index + 1) . '</span>';
                                                    $html .= '</div>';
                                                    $html .= '</div>';
                                                    $html .= '<div class="flex-1 min-w-0">';
                                                    $html .= '<p class="text-sm font-medium text-gray-900 truncate">' . htmlspecialchars($recipient['name']) . '</p>';
                                                    $html .= '<div class="flex items-center space-x-4 text-sm text-gray-500">';
                                                    $html .= '<span>📞 ' . htmlspecialchars($recipient['phone']) . '</span>';
                                                    if (!empty($recipient['student_name'])) {
                                                        $html .= '<span>👨‍👩‍👧‍👦 ' . htmlspecialchars($recipient['student_name']) . '</span>';
                                                    }
                                                    if (!empty($recipient['grade'])) {
                                                        $html .= '<span>🎓 ' . htmlspecialchars($recipient['grade']) . '</span>';
                                                    }
                                                    if (!empty($recipient['class_section'])) {
                                                        $html .= '<span>🏫 ' . htmlspecialchars($recipient['class_section']) . '</span>';
                                                    }
                                                    $html .= '</div>';
                                                    $html .= '</div>';
                                                    $html .= '</div>';
                                                    $html .= '</div>';
                                                    $html .= '</div>';
                                                }

                                                if ($this->recipientCount > 10) {
                                                    $html .= '<div class="px-4 py-3 bg-gray-50 text-center text-sm text-gray-500">';
                                                    $html .= 'And ' . ($this->recipientCount - 10) . ' more messages...';
                                                    $html .= '</div>';
                                                }

                                                $html .= '</div>';
                                                $html .= '</div>';
                                            }

                                            $html .= '</div>';

                                            return new HtmlString($html);
                                        }),
                                ])
                                ->visible(fn() => $this->recipientCount > 0 || count($this->recipientPreview) > 0)
                                ->columnSpanFull(),
                        ]),

                    Forms\Components\Wizard\Step::make('Compose Message')
                        ->schema([
                            Forms\Components\Section::make('Message Details')
                                ->schema([
                                    Forms\Components\TextInput::make('title')
                                        ->required()
                                        ->maxLength(255)
                                        ->placeholder('e.g., Fee Reminder January 2025'),

                                    Forms\Components\Select::make('template_id')
                                        ->label('Use Template')
                                        ->options(function () {
                                            return MessageTemplate::pluck('name', 'id')->toArray();
                                        })
                                        ->placeholder('Select a template or write your own message')
                                        ->afterStateUpdated(function ($state, Set $set) {
                                            if ($state) {
                                                $template = MessageTemplate::find($state);
                                                if ($template) {
                                                    $set('message', $template->content);
                                                }
                                            }
                                        }),

                                    Forms\Components\Textarea::make('message')
                                        ->required()
                                        ->placeholder('Enter your message here...')
                                        ->helperText('Available placeholders: {parent_name}, {student_name}, {grade}')
                                        ->rows(5)
                                        ->live()
                                        ->afterStateUpdated(function ($state, Set $set) {
                                            if ($state) {
                                                $messageParts = ceil(strlen($state) / 160);
                                                $cost = 0.50 * $messageParts * $this->recipientCount;
                                                $this->estimatedCost = $cost;
                                                $set('message_preview', $this->getMessagePreview($state));
                                            }
                                        }),

                                    Forms\Components\Grid::make()
                                        ->schema([
                                            Forms\Components\TextInput::make('character_count')
                                                ->label('Character Count')
                                                ->default(0)
                                                ->disabled()
                                                ->live()
                                                ->afterStateHydrated(function (Forms\Components\TextInput $component, $state, Set $set, Get $get) {
                                                    $message = $get('message') ?? '';
                                                    $set('character_count', strlen($message));
                                                }),

                                            Forms\Components\TextInput::make('sms_parts')
                                                ->label('SMS Parts')
                                                ->default(1)
                                                ->disabled()
                                                ->live()
                                                ->afterStateHydrated(function (Forms\Components\TextInput $component, $state, Set $set, Get $get) {
                                                    $message = $get('message') ?? '';
                                                    $parts = ceil(strlen($message) / 160);
                                                    $set('sms_parts', $parts);
                                                }),

                                            Forms\Components\TextInput::make('total_cost')
                                                ->label('Total Estimated Cost')
                                                ->prefix('ZMW')
                                                ->default(0)
                                                ->disabled()
                                                ->live()
                                                ->afterStateHydrated(function (Forms\Components\TextInput $component, $state, Set $set, Get $get) {
                                                    $message = $get('message') ?? '';
                                                    $parts = ceil(strlen($message) / 160);
                                                    $cost = 0.50 * $parts * $this->recipientCount;
                                                    $set('total_cost', number_format($cost, 2));
                                                }),
                                        ])
                                        ->columns(3),
                                ]),

                            Forms\Components\Section::make('Message Preview')
                                ->schema([
                                    Forms\Components\Placeholder::make('message_preview')
                                        ->label('Preview (First Recipient)')
                                        ->content(function (Get $get) {
                                            $message = $get('message');
                                            if (!$message) {
                                                return 'Enter a message to see the preview';
                                            }
                                            return $this->getMessagePreview($message);
                                        }),

                                    Forms\Components\Checkbox::make('save_as_template')
                                        ->label('Save this message as a template for future use'),

                                    Forms\Components\TextInput::make('template_name')
                                        ->label('Template Name')
                                        ->visible(fn (Get $get): bool => $get('save_as_template'))
                                        ->required(fn (Get $get): bool => $get('save_as_template')),
                                ]),
                        ]),

                    Forms\Components\Wizard\Step::make('Review & Send')
                        ->schema([
                            Forms\Components\Section::make('Broadcast Summary')
                                ->schema([
                                    Forms\Components\Placeholder::make('title_summary')
                                        ->label('Broadcast Title')
                                        ->content(fn (Get $get) => $get('title')),

                                    Forms\Components\Placeholder::make('recipients_summary')
                                        ->label('Total Messages')
                                        ->content(fn () => $this->recipientCount . ' messages (one per child)'),

                                    Forms\Components\Placeholder::make('message_summary')
                                        ->label('Message')
                                        ->content(fn (Get $get) => nl2br(htmlspecialchars($get('message')))),

                                    Forms\Components\Placeholder::make('cost_summary')
                                        ->label('Total Cost')
                                        ->content(fn () => 'ZMW ' . number_format($this->estimatedCost, 2)),
                                ]),

                            Forms\Components\Checkbox::make('confirm_send')
                                ->label('I confirm that I want to send these messages to all selected recipients')
                                ->required()
                                ->rules(['required', 'accepted']),
                        ]),
                ])
                ->submitAction(new HtmlString('<button type="submit" class="filament-button filament-button-size-md inline-flex items-center justify-center py-1 gap-1 font-medium rounded-lg border transition-colors outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset min-h-[2.25rem] px-4 text-sm text-white shadow focus:ring-white border-transparent bg-primary-600 hover:bg-primary-500 focus:bg-primary-700 focus:ring-offset-primary-700">Send Broadcast</button>'))
                ->columnSpanFull(),
            ])
            ->columns(1);
    }

    public function create_broadcast()
    {
        $data = $this->form->getState();

        if ($this->recipientCount === 0) {
            Notification::make()
                ->title('No Recipients Selected')
                ->body('Please select at least one recipient before sending the broadcast.')
                ->warning()
                ->send();
            return;
        }

        DB::beginTransaction();

        try {
            if (!empty($data['save_as_template']) && !empty($data['template_name'])) {
                MessageTemplate::create([
                    'name' => $data['template_name'],
                    'content' => $data['message'],
                    'created_by' => Auth::id(),
                ]);
            }

            $broadcast = MessageBroadcast::create([
                'title' => $data['title'],
                'message' => $data['message'],
                'filters' => [
                    'recipient_type' => $data['recipient_type'],
                    'grade_id' => $data['grade_id'] ?? null,
                    'fee_status' => $data['fee_status'] ?? null,
                ],
                'total_recipients' => $this->recipientCount,
                'status' => 'draft',
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            Notification::make()
                ->title('Broadcast Created')
                ->body('Your broadcast has been created successfully. Proceed to send the messages.')
                ->success()
                ->send();

            $this->redirect(CommunicationCenterResource::getUrl('send-broadcast', ['record' => $broadcast->id]));
        } catch (\Exception $e) {
            DB::rollBack();

            Notification::make()
                ->title('Error Creating Broadcast')
                ->body('An error occurred: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getMessagePreview($message)
    {
        if (empty($this->recipientPreview)) {
            return "No recipients selected for preview";
        }

        $preview = $message;
        $recipient = $this->recipientPreview[0] ?? null;

        if ($recipient) {
            if (isset($recipient['student_name'])) {
                $preview = str_replace('{student_name}', $recipient['student_name'], $preview);
            }
            if (isset($recipient['name'])) {
                $preview = str_replace('{parent_name}', $recipient['name'], $preview);
            }
            if (isset($recipient['grade'])) {
                $preview = str_replace('{grade}', $recipient['grade'], $preview);
            }
        }

        return $preview;
    }

    public function previewRecipients()
    {
        try {
            // Get the raw form data without validation
            $data = $this->form->getRawState();
            $recipientType = $data['recipient_type'] ?? 'parents';

            Log::info('Preview Recipients Debug - Step 1: Form Data', [
                'recipient_type' => $recipientType,
                'grade_id' => $data['grade_id'] ?? 'null',
                'fee_status' => $data['fee_status'] ?? 'null',
                'all_data' => $data
            ]);

            if ($recipientType === 'parents') {
                // Step 1: Check basic parent data
                $totalParents = ParentGuardian::count();
                $parentsWithPhone = ParentGuardian::whereNotNull('phone')->count();

                Log::info('Preview Recipients Debug - Step 2: Basic Counts', [
                    'total_parents' => $totalParents,
                    'parents_with_phone' => $parentsWithPhone
                ]);

                // Start with base query
                $query = ParentGuardian::whereNotNull('phone')->whereHas('students');

                // Apply grade filter if selected
                if (!empty($data['grade_id'])) {
                    Log::info("Applying grade filter: {$data['grade_id']}");

                    $query->whereHas('students', function ($q) use ($data) {
                        $q->whereHas('classSection', function ($csq) use ($data) {
                            $csq->where('grade_id', $data['grade_id']);
                        });
                    });
                }

                // Apply fee status filter if not "all"
                if (!empty($data['fee_status']) && $data['fee_status'] !== 'all') {
                    Log::info("Applying fee status filter: {$data['fee_status']}");

                    $query->whereHas('students', function ($studentQuery) use ($data) {
                        $studentQuery->whereHas('fees', function ($feeQuery) use ($data) {
                            if ($data['fee_status'] === 'paid') {
                                $feeQuery->where('payment_status', 'paid');
                            } elseif ($data['fee_status'] === 'partial') {
                                $feeQuery->where('payment_status', 'partial');
                            } elseif ($data['fee_status'] === 'unpaid') {
                                $feeQuery->where('payment_status', 'unpaid');
                            }
                        });
                    });
                }

                // Get parents with their students
                $parents = $query->with(['students.classSection.grade'])->get();
                Log::info("Parents found: " . $parents->count());

                $formattedRecipients = [];
                $previewRecipients = [];
                $totalMessages = 0;

                foreach ($parents as $parent) {
                    Log::info("Processing parent: {$parent->name} (ID: {$parent->id}, Phone: {$parent->phone})");

                    // Get all students for this parent
                    $allStudents = $parent->students()->with('classSection.grade')->get();
                    Log::info("Parent has {$allStudents->count()} students");

                    // Filter students based on criteria
                    $qualifyingStudents = $allStudents;

                    // Apply grade filter to students if specified
                    if (!empty($data['grade_id'])) {
                        $qualifyingStudents = $qualifyingStudents->filter(function ($student) use ($data) {
                            return $student->classSection && $student->classSection->grade_id == $data['grade_id'];
                        });
                    }

                    Log::info("Qualifying students: {$qualifyingStudents->count()}");

                    // Create one message entry per qualifying student
                    foreach ($qualifyingStudents as $student) {
                        $gradeName = $student->classSection?->grade?->name ?? 'Unknown Grade';
                        $classSectionName = $student->classSection?->name ?? 'Unknown Class';

                        Log::info("Adding message for student: {$student->name} in {$gradeName}");

                        $formattedRecipients[] = [
                            'id' => $parent->id,
                            'name' => $parent->name,
                            'phone' => $parent->phone,
                            'student_name' => $student->name,
                            'grade' => $gradeName,
                            'class_section' => $classSectionName,
                            'student_id' => $student->id,
                        ];

                        if (count($previewRecipients) < 10) {
                            $previewRecipients[] = [
                                'name' => $parent->name . " (for " . $student->name . ")",
                                'phone' => $parent->phone,
                                'student_name' => $student->name,
                                'grade' => $gradeName,
                                'class_section' => $classSectionName,
                            ];
                        }

                        $totalMessages++;
                    }
                }

                $this->recipients = $formattedRecipients;
                $this->recipientPreview = $previewRecipients;
                $this->recipientCount = $totalMessages;
                $this->estimatedCost = $totalMessages * 0.50;

                Log::info("Final results: {$totalMessages} messages, Cost: {$this->estimatedCost}");

                Notification::make()
                    ->title('Recipients Found')
                    ->body("Found {$totalMessages} messages to send. Parents with multiple children will receive separate SMS for each child.")
                    ->success()
                    ->send();

            } else {
                $this->recipients = [];
                $this->recipientPreview = [];
                $this->recipientCount = 0;
                $this->estimatedCost = 0;

                Notification::make()
                    ->title('Feature Not Available')
                    ->body('Staff and teacher filtering is not implemented in this basic version.')
                    ->warning()
                    ->send();
            }

        } catch (\Exception $e) {
            Log::error('Error in previewRecipients', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            Notification::make()
                ->title('Error')
                ->body('An error occurred while fetching recipients: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}
