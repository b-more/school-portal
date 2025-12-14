<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SchoolSettingsResource\Pages;
use App\Models\SchoolSettings;
use App\Constants\RoleConstants;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;

class SchoolSettingsResource extends Resource
{
    protected static ?string $model = SchoolSettings::class;
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'System Configuration';
    protected static ?string $navigationLabel = 'School Settings';
    protected static ?string $modelLabel = 'School Settings';
    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        if (!$user) return false;

        return in_array($user->role_id, [
            RoleConstants::ADMIN,
            RoleConstants::HEAD_TEACHER_PRIMARY,
            RoleConstants::HEAD_TEACHER_SECONDARY,
        ]);
    }

    public static function canAccess(): bool
    {
        return static::shouldRegisterNavigation();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Settings')
                    ->tabs([
                        // =============================================
                        // TAB 1: SCHOOL IDENTITY
                        // =============================================
                        Forms\Components\Tabs\Tab::make('School Identity')
                            ->icon('heroicon-o-building-library')
                            ->schema([
                                Forms\Components\Section::make('Basic Information')
                                    ->description('Core school identification details')
                                    ->schema([
                                        Forms\Components\TextInput::make('school_name')
                                            ->label('School Name')
                                            ->required()
                                            ->maxLength(255)
                                            ->columnSpan(2),

                                        Forms\Components\TextInput::make('school_code')
                                            ->label('School Code')
                                            ->maxLength(50)
                                            ->helperText('Unique identifier for the school'),

                                        Forms\Components\TextInput::make('registration_number')
                                            ->label('Registration Number')
                                            ->maxLength(100)
                                            ->helperText('Official government registration'),

                                        Forms\Components\TextInput::make('tax_pin')
                                            ->label('Tax PIN / TPIN')
                                            ->maxLength(50),

                                        Forms\Components\TextInput::make('school_motto')
                                            ->label('School Motto')
                                            ->maxLength(255)
                                            ->columnSpan(2),

                                        Forms\Components\Textarea::make('school_vision')
                                            ->label('Vision Statement')
                                            ->rows(2)
                                            ->columnSpan(2),

                                        Forms\Components\Textarea::make('school_mission')
                                            ->label('Mission Statement')
                                            ->rows(2)
                                            ->columnSpan(2),
                                    ])
                                    ->columns(2),

                                Forms\Components\Section::make('Branding & Logos')
                                    ->description('Upload school logos and set brand colors')
                                    ->schema([
                                        Forms\Components\FileUpload::make('school_logo')
                                            ->label('Primary Logo')
                                            ->image()
                                            ->disk('public')
                                            ->directory('school-logos')
                                            ->visibility('public')
                                            ->imagePreviewHeight('100')
                                            ->maxSize(2048)
                                            ->helperText('Main school logo (max 2MB)'),

                                        Forms\Components\FileUpload::make('report_card_logo')
                                            ->label('Report Card Logo')
                                            ->image()
                                            ->disk('public')
                                            ->directory('school-logos')
                                            ->visibility('public')
                                            ->imagePreviewHeight('100')
                                            ->maxSize(2048)
                                            ->helperText('Logo for report cards'),

                                        Forms\Components\FileUpload::make('favicon')
                                            ->label('Favicon')
                                            ->acceptedFileTypes(['image/x-icon', 'image/vnd.microsoft.icon', 'image/png', 'image/ico', 'image/svg+xml'])
                                            ->disk('public')
                                            ->directory('school-logos')
                                            ->visibility('public')
                                            ->maxSize(512)
                                            ->helperText('Upload .ico, .png or .svg file (recommended: 32x32 or 64x64 pixels)'),

                                        Forms\Components\FileUpload::make('header_logo')
                                            ->label('Header Logo')
                                            ->image()
                                            ->disk('public')
                                            ->directory('school-logos')
                                            ->visibility('public')
                                            ->imagePreviewHeight('100')
                                            ->maxSize(2048)
                                            ->helperText('Logo for PDF headers'),

                                        Forms\Components\ColorPicker::make('primary_color')
                                            ->label('Primary Color')
                                            ->default('#1e40af'),

                                        Forms\Components\ColorPicker::make('secondary_color')
                                            ->label('Secondary Color')
                                            ->default('#64748b'),

                                        Forms\Components\ColorPicker::make('accent_color')
                                            ->label('Accent Color')
                                            ->default('#f59e0b'),
                                    ])
                                    ->columns(4),
                            ]),

                        // =============================================
                        // TAB 2: CONTACT INFORMATION
                        // =============================================
                        Forms\Components\Tabs\Tab::make('Contact Info')
                            ->icon('heroicon-o-phone')
                            ->schema([
                                Forms\Components\Section::make('Address')
                                    ->schema([
                                        Forms\Components\TextInput::make('address')
                                            ->label('Street Address')
                                            ->maxLength(255)
                                            ->columnSpan(2),

                                        Forms\Components\TextInput::make('city')
                                            ->maxLength(100),

                                        Forms\Components\TextInput::make('state_province')
                                            ->label('State/Province')
                                            ->maxLength(100),

                                        Forms\Components\TextInput::make('postal_code')
                                            ->label('Postal Code')
                                            ->maxLength(20),

                                        Forms\Components\TextInput::make('country')
                                            ->maxLength(100)
                                            ->default('Zambia'),
                                    ])
                                    ->columns(2),

                                Forms\Components\Section::make('Contact Details')
                                    ->schema([
                                        Forms\Components\TextInput::make('phone')
                                            ->label('Primary Phone')
                                            ->tel()
                                            ->maxLength(20),

                                        Forms\Components\TextInput::make('alternate_phone')
                                            ->label('Alternate Phone')
                                            ->tel()
                                            ->maxLength(20),

                                        Forms\Components\TextInput::make('email')
                                            ->email()
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('website')
                                            ->maxLength(255)
                                            ->placeholder('www.example.com')
                                            ->helperText('Enter website address (e.g., www.stfrancisofassisi.tech)'),
                                    ])
                                    ->columns(2),

                                Forms\Components\Section::make('Social Media')
                                    ->schema([
                                        Forms\Components\KeyValue::make('social_media_links')
                                            ->label('Social Media Links')
                                            ->keyLabel('Platform')
                                            ->valueLabel('URL')
                                            ->addActionLabel('Add Social Media')
                                            ->reorderable()
                                            ->columnSpanFull(),
                                    ])
                                    ->collapsible(),
                            ]),

                        // =============================================
                        // TAB 3: ACADEMIC SETTINGS
                        // =============================================
                        Forms\Components\Tabs\Tab::make('Academic')
                            ->icon('heroicon-o-academic-cap')
                            ->schema([
                                Forms\Components\Section::make('Academic Year')
                                    ->schema([
                                        Forms\Components\Select::make('academic_year_format')
                                            ->label('Academic Year Format')
                                            ->options([
                                                'YYYY' => 'Single Year (e.g., 2025)',
                                                'YYYY-YYYY' => 'Range (e.g., 2025-2026)',
                                            ])
                                            ->default('YYYY'),

                                        Forms\Components\Select::make('terms_per_year')
                                            ->label('Terms Per Year')
                                            ->options([
                                                2 => '2 Terms (Semesters)',
                                                3 => '3 Terms',
                                                4 => '4 Terms (Quarters)',
                                            ])
                                            ->default(3),
                                    ])
                                    ->columns(2),

                                Forms\Components\Section::make('Grading System')
                                    ->schema([
                                        Forms\Components\Select::make('grading_system')
                                            ->label('Grading System')
                                            ->options([
                                                'percentage' => 'Percentage (0-100%)',
                                                'letter' => 'Letter Grades (A-F)',
                                                'gpa' => 'GPA (0.0-4.0)',
                                            ])
                                            ->default('percentage')
                                            ->live(),

                                        Forms\Components\TextInput::make('max_mark')
                                            ->label('Maximum Mark')
                                            ->numeric()
                                            ->default(100)
                                            ->minValue(1)
                                            ->maxValue(1000),

                                        Forms\Components\TextInput::make('passing_mark')
                                            ->label('Passing Mark (%)')
                                            ->numeric()
                                            ->default(40)
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->suffix('%'),

                                        Forms\Components\Toggle::make('show_position_in_class')
                                            ->label('Show Position in Class')
                                            ->default(true),

                                        Forms\Components\Toggle::make('show_position_in_grade')
                                            ->label('Show Position in Grade')
                                            ->default(true),

                                        Forms\Components\Toggle::make('show_grade_average')
                                            ->label('Show Grade Average')
                                            ->default(true),
                                    ])
                                    ->columns(3),

                                Forms\Components\Section::make('Continuous Assessment')
                                    ->schema([
                                        Forms\Components\Toggle::make('enable_continuous_assessment')
                                            ->label('Enable Continuous Assessment (CA)')
                                            ->default(true)
                                            ->live()
                                            ->columnSpanFull(),

                                        Forms\Components\TextInput::make('ca_weight_percentage')
                                            ->label('CA Weight')
                                            ->numeric()
                                            ->default(40)
                                            ->suffix('%')
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->visible(fn (Forms\Get $get) => $get('enable_continuous_assessment')),

                                        Forms\Components\TextInput::make('exam_weight_percentage')
                                            ->label('Exam Weight')
                                            ->numeric()
                                            ->default(60)
                                            ->suffix('%')
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->visible(fn (Forms\Get $get) => $get('enable_continuous_assessment')),
                                    ])
                                    ->columns(2),

                                Forms\Components\Section::make('Grading Scale (Zambian Primary School Standard)')
                                    ->description('Configure grade boundaries for student assessment. Default is the Zambian ECZ standard.')
                                    ->schema([
                                        Forms\Components\Placeholder::make('grading_info')
                                            ->content('Configure the minimum marks for each grade. Marks below Grade E minimum will be recorded as "E" (Fail).')
                                            ->columnSpanFull(),

                                        Forms\Components\TextInput::make('grade_a_min')
                                            ->label('Grade A (Distinction)')
                                            ->numeric()
                                            ->default(80)
                                            ->suffix('- 100%')
                                            ->helperText('Excellent performance'),

                                        Forms\Components\TextInput::make('grade_b_min')
                                            ->label('Grade B (Merit)')
                                            ->numeric()
                                            ->default(65)
                                            ->suffix('- 79%')
                                            ->helperText('Very good performance'),

                                        Forms\Components\TextInput::make('grade_c_min')
                                            ->label('Grade C (Credit)')
                                            ->numeric()
                                            ->default(50)
                                            ->suffix('- 64%')
                                            ->helperText('Good performance'),

                                        Forms\Components\TextInput::make('grade_d_min')
                                            ->label('Grade D (Pass)')
                                            ->numeric()
                                            ->default(40)
                                            ->suffix('- 49%')
                                            ->helperText('Satisfactory performance'),

                                        Forms\Components\TextInput::make('grade_e_min')
                                            ->label('Grade E (Fail)')
                                            ->numeric()
                                            ->default(0)
                                            ->suffix('- 39%')
                                            ->helperText('Below passing mark'),

                                        Forms\Components\Placeholder::make('grading_summary')
                                            ->label('Grading Summary')
                                            ->content(function (Forms\Get $get) {
                                                $a = $get('grade_a_min') ?? 80;
                                                $b = $get('grade_b_min') ?? 65;
                                                $c = $get('grade_c_min') ?? 50;
                                                $d = $get('grade_d_min') ?? 40;
                                                $e = $get('grade_e_min') ?? 0;

                                                return "
                                                    A: {$a}% - 100% (Distinction)
                                                    B: {$b}% - " . ($a - 1) . "% (Merit)
                                                    C: {$c}% - " . ($b - 1) . "% (Credit)
                                                    D: {$d}% - " . ($c - 1) . "% (Pass)
                                                    E: {$e}% - " . ($d - 1) . "% (Fail)
                                                ";
                                            })
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(5)
                                    ->collapsible(),
                            ]),

                        // =============================================
                        // TAB 4: ATTENDANCE SETTINGS
                        // =============================================
                        Forms\Components\Tabs\Tab::make('Attendance')
                            ->icon('heroicon-o-clipboard-document-check')
                            ->schema([
                                Forms\Components\Section::make('School Hours')
                                    ->schema([
                                        Forms\Components\TimePicker::make('school_start_time')
                                            ->label('School Start Time')
                                            ->default('07:30')
                                            ->seconds(false),

                                        Forms\Components\TimePicker::make('school_end_time')
                                            ->label('School End Time')
                                            ->default('13:00')
                                            ->seconds(false),

                                        Forms\Components\TextInput::make('late_arrival_minutes')
                                            ->label('Late After (minutes)')
                                            ->numeric()
                                            ->default(15)
                                            ->suffix('minutes')
                                            ->helperText('Students arriving after this many minutes are marked late'),
                                    ])
                                    ->columns(3),

                                Forms\Components\Section::make('School Days')
                                    ->schema([
                                        Forms\Components\CheckboxList::make('school_days')
                                            ->label('School Days')
                                            ->options([
                                                1 => 'Monday',
                                                2 => 'Tuesday',
                                                3 => 'Wednesday',
                                                4 => 'Thursday',
                                                5 => 'Friday',
                                                6 => 'Saturday',
                                                7 => 'Sunday',
                                            ])
                                            ->default([1, 2, 3, 4, 5])
                                            ->columns(4)
                                            ->columnSpanFull(),
                                    ]),

                                Forms\Components\Section::make('Notifications')
                                    ->schema([
                                        Forms\Components\Toggle::make('notify_parent_on_absence')
                                            ->label('Notify Parents on Absence')
                                            ->default(true),

                                        Forms\Components\Toggle::make('notify_parent_on_late')
                                            ->label('Notify Parents on Late Arrival')
                                            ->default(false),

                                        Forms\Components\TextInput::make('absence_notification_threshold')
                                            ->label('Absence Alert Threshold')
                                            ->numeric()
                                            ->default(3)
                                            ->suffix('absences')
                                            ->helperText('Alert admin after this many consecutive absences'),
                                    ])
                                    ->columns(3),
                            ]),

                        // =============================================
                        // TAB 5: FEE SETTINGS
                        // =============================================
                        Forms\Components\Tabs\Tab::make('Fees & Payments')
                            ->icon('heroicon-o-banknotes')
                            ->schema([
                                Forms\Components\Section::make('Payment Options')
                                    ->schema([
                                        Forms\Components\Toggle::make('enable_online_payments')
                                            ->label('Enable Online Payments')
                                            ->default(false),

                                        Forms\Components\Toggle::make('enable_partial_payments')
                                            ->label('Allow Partial Payments')
                                            ->default(true)
                                            ->live(),

                                        Forms\Components\TextInput::make('minimum_partial_payment')
                                            ->label('Minimum Partial Payment')
                                            ->numeric()
                                            ->default(100)
                                            ->prefix('K')
                                            ->visible(fn (Forms\Get $get) => $get('enable_partial_payments')),

                                        Forms\Components\CheckboxList::make('payment_methods')
                                            ->label('Accepted Payment Methods')
                                            ->options([
                                                'cash' => 'Cash',
                                                'bank_transfer' => 'Bank Transfer',
                                                'mobile_money' => 'Mobile Money',
                                                'cheque' => 'Cheque',
                                                'card' => 'Debit/Credit Card',
                                            ])
                                            ->default(['cash', 'bank_transfer', 'mobile_money'])
                                            ->columns(3)
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(3),

                                Forms\Components\Section::make('Late Fees')
                                    ->schema([
                                        Forms\Components\Toggle::make('enable_late_fees')
                                            ->label('Enable Late Fees')
                                            ->default(true)
                                            ->live(),

                                        Forms\Components\TextInput::make('late_fee_percentage')
                                            ->label('Late Fee Rate')
                                            ->numeric()
                                            ->default(5)
                                            ->suffix('%')
                                            ->visible(fn (Forms\Get $get) => $get('enable_late_fees')),

                                        Forms\Components\TextInput::make('grace_period_days')
                                            ->label('Grace Period')
                                            ->numeric()
                                            ->default(7)
                                            ->suffix('days')
                                            ->visible(fn (Forms\Get $get) => $get('enable_late_fees')),
                                    ])
                                    ->columns(3),

                                Forms\Components\Section::make('Invoice & Receipt Settings')
                                    ->schema([
                                        Forms\Components\TextInput::make('invoice_prefix')
                                            ->label('Invoice Prefix')
                                            ->default('INV')
                                            ->maxLength(10),

                                        Forms\Components\TextInput::make('receipt_prefix')
                                            ->label('Receipt Prefix')
                                            ->default('RCP')
                                            ->maxLength(10),

                                        Forms\Components\Textarea::make('payment_instructions')
                                            ->label('Payment Instructions')
                                            ->rows(3)
                                            ->placeholder('Instructions displayed on invoices...')
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2),

                                Forms\Components\Section::make('Bank Details')
                                    ->schema([
                                        Forms\Components\TextInput::make('bank_details.bank_name')
                                            ->label('Bank Name'),

                                        Forms\Components\TextInput::make('bank_details.account_name')
                                            ->label('Account Name'),

                                        Forms\Components\TextInput::make('bank_details.account_number')
                                            ->label('Account Number'),

                                        Forms\Components\TextInput::make('bank_details.branch')
                                            ->label('Branch'),

                                        Forms\Components\TextInput::make('bank_details.swift_code')
                                            ->label('SWIFT Code'),
                                    ])
                                    ->columns(3)
                                    ->collapsible(),

                                Forms\Components\Section::make('Mobile Money Details')
                                    ->schema([
                                        Forms\Components\Select::make('mobile_money_details.provider')
                                            ->label('Provider')
                                            ->options([
                                                'MTN' => 'MTN Mobile Money',
                                                'Airtel' => 'Airtel Money',
                                                'Zamtel' => 'Zamtel Kwacha',
                                            ]),

                                        Forms\Components\TextInput::make('mobile_money_details.name')
                                            ->label('Registered Name'),

                                        Forms\Components\TextInput::make('mobile_money_details.number')
                                            ->label('Mobile Number')
                                            ->tel(),
                                    ])
                                    ->columns(3)
                                    ->collapsible(),
                            ]),

                        // =============================================
                        // TAB 6: SMS & COMMUNICATION
                        // =============================================
                        Forms\Components\Tabs\Tab::make('Communication')
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->schema([
                                Forms\Components\Section::make('SMS Settings')
                                    ->schema([
                                        Forms\Components\TextInput::make('sms_sender_id')
                                            ->label('SMS Sender ID')
                                            ->maxLength(11)
                                            ->helperText('Max 11 characters, displayed as sender'),

                                        Forms\Components\TextInput::make('sms_balance_alert_threshold')
                                            ->label('Low Balance Alert')
                                            ->numeric()
                                            ->default(100)
                                            ->suffix('SMS')
                                            ->helperText('Alert when balance falls below this'),

                                        Forms\Components\Toggle::make('enable_sms_notifications')
                                            ->label('Enable SMS Notifications')
                                            ->default(true)
                                            ->live(),

                                        Forms\Components\Toggle::make('enable_email_notifications')
                                            ->label('Enable Email Notifications')
                                            ->default(true),

                                        Forms\Components\Toggle::make('enable_whatsapp_notifications')
                                            ->label('Enable WhatsApp Notifications')
                                            ->default(false),
                                    ])
                                    ->columns(3),

                                Forms\Components\Section::make('Automatic SMS Triggers')
                                    ->description('Configure which events trigger automatic SMS notifications')
                                    ->schema([
                                        Forms\Components\Toggle::make('sms_on_fee_payment')
                                            ->label('Fee Payment Confirmation')
                                            ->default(true)
                                            ->helperText('Send SMS when fee is paid'),

                                        Forms\Components\Toggle::make('sms_on_result_release')
                                            ->label('Results Released')
                                            ->default(true)
                                            ->helperText('Send SMS when results are published'),

                                        Forms\Components\Toggle::make('sms_on_attendance')
                                            ->label('Attendance Alerts')
                                            ->default(false)
                                            ->helperText('Send SMS for absences/late arrivals'),

                                        Forms\Components\Toggle::make('sms_on_homework')
                                            ->label('Homework Assignments')
                                            ->default(false)
                                            ->helperText('Send SMS when homework is assigned'),
                                    ])
                                    ->columns(2)
                                    ->visible(fn (Forms\Get $get) => $get('enable_sms_notifications')),
                            ]),

                        // =============================================
                        // TAB 7: REPORT CARDS
                        // =============================================
                        Forms\Components\Tabs\Tab::make('Report Cards')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Forms\Components\Section::make('Report Card Format')
                                    ->schema([
                                        Forms\Components\Select::make('report_card_format')
                                            ->label('Format Style')
                                            ->options([
                                                'standard' => 'Standard (Full Details)',
                                                'detailed' => 'Detailed (With Analytics)',
                                                'minimal' => 'Minimal (Grades Only)',
                                            ])
                                            ->default('standard'),
                                    ]),

                                Forms\Components\Section::make('Display Options')
                                    ->schema([
                                        Forms\Components\Toggle::make('show_teacher_comments')
                                            ->label('Class Teacher Comments')
                                            ->default(true),

                                        Forms\Components\Toggle::make('show_headteacher_comments')
                                            ->label('Head Teacher Comments')
                                            ->default(true),

                                        Forms\Components\Toggle::make('show_attendance_summary')
                                            ->label('Attendance Summary')
                                            ->default(true),

                                        Forms\Components\Toggle::make('show_conduct_grade')
                                            ->label('Conduct/Behavior Grade')
                                            ->default(true),

                                        Forms\Components\Toggle::make('show_principal_signature')
                                            ->label('Principal Signature')
                                            ->default(true),

                                        Forms\Components\Toggle::make('show_class_teacher_signature')
                                            ->label('Class Teacher Signature')
                                            ->default(true),

                                        Forms\Components\Toggle::make('show_parent_signature_line')
                                            ->label('Parent Signature Line')
                                            ->default(true),
                                    ])
                                    ->columns(4),

                                Forms\Components\Section::make('Signatures')
                                    ->schema([
                                        Forms\Components\TextInput::make('principal_name')
                                            ->label('Principal/Director Name'),

                                        Forms\Components\TextInput::make('principal_title')
                                            ->label('Title')
                                            ->default('Executive Director'),

                                        Forms\Components\FileUpload::make('principal_signature')
                                            ->label('Signature Image')
                                            ->image()
                                            ->disk('public')
                                            ->directory('signatures')
                                            ->visibility('public')
                                            ->imagePreviewHeight('60')
                                            ->maxSize(512),
                                    ])
                                    ->columns(3),

                                Forms\Components\Section::make('Next Term Information')
                                    ->schema([
                                        Forms\Components\DatePicker::make('next_term_starts')
                                            ->label('Next Term Starts'),

                                        Forms\Components\DatePicker::make('next_term_ends')
                                            ->label('Next Term Ends'),

                                        Forms\Components\Textarea::make('report_card_footer_text')
                                            ->label('Footer Text')
                                            ->rows(2)
                                            ->placeholder('Additional information printed at bottom of report card...')
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2),
                            ]),

                        // =============================================
                        // TAB 8: SECTION HEADS
                        // =============================================
                        Forms\Components\Tabs\Tab::make('Section Heads')
                            ->icon('heroicon-o-user-group')
                            ->schema([
                                Forms\Components\Section::make('Primary Section')
                                    ->schema([
                                        Forms\Components\TextInput::make('primary_head_name')
                                            ->label('Head Teacher Name'),

                                        Forms\Components\TextInput::make('primary_head_title')
                                            ->label('Title')
                                            ->default('Head Teacher Primary'),

                                        Forms\Components\FileUpload::make('primary_head_signature')
                                            ->label('Signature')
                                            ->image()
                                            ->disk('public')
                                            ->directory('signatures')
                                            ->visibility('public')
                                            ->imagePreviewHeight('60')
                                            ->maxSize(512),
                                    ])
                                    ->columns(3),

                                Forms\Components\Section::make('Secondary Section')
                                    ->schema([
                                        Forms\Components\TextInput::make('secondary_head_name')
                                            ->label('Head Teacher Name'),

                                        Forms\Components\TextInput::make('secondary_head_title')
                                            ->label('Title')
                                            ->default('Head Teacher Secondary'),

                                        Forms\Components\FileUpload::make('secondary_head_signature')
                                            ->label('Signature')
                                            ->image()
                                            ->disk('public')
                                            ->directory('signatures')
                                            ->visibility('public')
                                            ->imagePreviewHeight('60')
                                            ->maxSize(512),
                                    ])
                                    ->columns(3),

                                Forms\Components\Section::make('Overall Head')
                                    ->schema([
                                        Forms\Components\TextInput::make('school_head_name')
                                            ->label('School Head Name'),

                                        Forms\Components\TextInput::make('school_head_title')
                                            ->label('Title')
                                            ->default('Head Teacher'),
                                    ])
                                    ->columns(2),
                            ]),

                        // =============================================
                        // TAB 9: SYSTEM SETTINGS
                        // =============================================
                        Forms\Components\Tabs\Tab::make('System')
                            ->icon('heroicon-o-cog')
                            ->schema([
                                Forms\Components\Section::make('Regional Settings')
                                    ->schema([
                                        Forms\Components\Select::make('currency_code')
                                            ->label('Currency')
                                            ->options([
                                                'ZMW' => 'Zambian Kwacha (K)',
                                                'USD' => 'US Dollar ($)',
                                                'GBP' => 'British Pound (£)',
                                                'EUR' => 'Euro (€)',
                                                'ZAR' => 'South African Rand (R)',
                                            ])
                                            ->default('ZMW')
                                            ->required(),

                                        Forms\Components\Select::make('timezone')
                                            ->label('Timezone')
                                            ->options([
                                                'Africa/Lusaka' => 'Lusaka (CAT, GMT+2)',
                                                'Africa/Johannesburg' => 'Johannesburg (SAST, GMT+2)',
                                                'Africa/Nairobi' => 'Nairobi (EAT, GMT+3)',
                                                'UTC' => 'UTC',
                                            ])
                                            ->default('Africa/Lusaka')
                                            ->required(),

                                        Forms\Components\Select::make('date_format')
                                            ->label('Date Format')
                                            ->options([
                                                'd/m/Y' => 'DD/MM/YYYY (31/12/2025)',
                                                'm/d/Y' => 'MM/DD/YYYY (12/31/2025)',
                                                'Y-m-d' => 'YYYY-MM-DD (2025-12-31)',
                                                'd M Y' => 'DD Mon YYYY (31 Dec 2025)',
                                                'F j, Y' => 'Month DD, YYYY (December 31, 2025)',
                                            ])
                                            ->default('d/m/Y'),

                                        Forms\Components\Select::make('time_format')
                                            ->label('Time Format')
                                            ->options([
                                                'H:i' => '24-hour (14:30)',
                                                'h:i A' => '12-hour (02:30 PM)',
                                            ])
                                            ->default('H:i'),
                                    ])
                                    ->columns(4),

                                Forms\Components\Section::make('Portal Settings')
                                    ->schema([
                                        Forms\Components\Toggle::make('enable_student_portal')
                                            ->label('Enable Student Portal')
                                            ->default(true),

                                        Forms\Components\Toggle::make('enable_parent_portal')
                                            ->label('Enable Parent Portal')
                                            ->default(true),

                                        Forms\Components\Toggle::make('enable_teacher_portal')
                                            ->label('Enable Teacher Portal')
                                            ->default(true),

                                        Forms\Components\TextInput::make('session_timeout_minutes')
                                            ->label('Session Timeout')
                                            ->numeric()
                                            ->default(120)
                                            ->suffix('minutes'),
                                    ])
                                    ->columns(4),

                                Forms\Components\Section::make('Security Settings')
                                    ->schema([
                                        Forms\Components\Toggle::make('require_password_change_on_first_login')
                                            ->label('Require Password Change on First Login')
                                            ->default(true),

                                        Forms\Components\TextInput::make('password_expiry_days')
                                            ->label('Password Expiry')
                                            ->numeric()
                                            ->default(90)
                                            ->suffix('days')
                                            ->helperText('0 = never expires'),

                                        Forms\Components\TextInput::make('max_login_attempts')
                                            ->label('Max Login Attempts')
                                            ->numeric()
                                            ->default(5),

                                        Forms\Components\TextInput::make('lockout_duration_minutes')
                                            ->label('Lockout Duration')
                                            ->numeric()
                                            ->default(30)
                                            ->suffix('minutes'),
                                    ])
                                    ->columns(4),

                                Forms\Components\Section::make('Maintenance Mode')
                                    ->schema([
                                        Forms\Components\Toggle::make('enable_maintenance_mode')
                                            ->label('Enable Maintenance Mode')
                                            ->default(false)
                                            ->live()
                                            ->helperText('Only admins can access when enabled'),

                                        Forms\Components\Textarea::make('maintenance_message')
                                            ->label('Maintenance Message')
                                            ->rows(2)
                                            ->placeholder('The system is currently under maintenance...')
                                            ->visible(fn (Forms\Get $get) => $get('enable_maintenance_mode'))
                                            ->columnSpanFull(),
                                    ]),

                                Forms\Components\Section::make('Backup Settings')
                                    ->schema([
                                        Forms\Components\Toggle::make('enable_auto_backup')
                                            ->label('Enable Automatic Backups')
                                            ->default(false)
                                            ->live(),

                                        Forms\Components\Select::make('backup_frequency')
                                            ->label('Backup Frequency')
                                            ->options([
                                                'daily' => 'Daily',
                                                'weekly' => 'Weekly',
                                                'monthly' => 'Monthly',
                                            ])
                                            ->default('daily')
                                            ->visible(fn (Forms\Get $get) => $get('enable_auto_backup')),

                                        Forms\Components\TimePicker::make('backup_time')
                                            ->label('Backup Time')
                                            ->default('02:00')
                                            ->seconds(false)
                                            ->visible(fn (Forms\Get $get) => $get('enable_auto_backup')),

                                        Forms\Components\TextInput::make('backup_retention_days')
                                            ->label('Retention Period')
                                            ->numeric()
                                            ->default(30)
                                            ->suffix('days')
                                            ->visible(fn (Forms\Get $get) => $get('enable_auto_backup')),
                                    ])
                                    ->columns(4)
                                    ->collapsible(),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->persistTabInQueryString(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageSchoolSettings::route('/'),
        ];
    }
}
