<?php

namespace App\Filament\Pages;

use App\Constants\RoleConstants;
use App\Models\Teacher;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class TeacherProfile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static string $view = 'filament.pages.teacher-profile';
    protected static ?string $navigationLabel = 'My Profile';
    protected static ?int $navigationSort = 100;

    public ?array $data = [];

    public function mount(): void
    {
        $teacher = $this->getTeacher();

        if ($teacher) {
            $this->form->fill([
                'name' => $teacher->name,
                'email' => $teacher->email,
                'phone' => $teacher->phone,
                'address' => $teacher->address,
                'nrc' => $teacher->nrc,
                'tpin' => $teacher->tpin,
                'account_number' => $teacher->account_number,
                'bank_name' => $teacher->bank_name,
                'bank_branch' => $teacher->bank_branch,
                'biography' => $teacher->biography,
                'profile_photo' => $teacher->profile_photo,
            ]);
        }
    }

    public function getTeacher()
    {
        $user = Auth::user();
        return Teacher::where('user_id', $user->id)->first();
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make('Personal Information')
                ->description('Update your personal information')
                ->schema([
                    TextInput::make('name')
                        ->label('Full Name')
                        ->required()
                        ->maxLength(255)
                        ->disabled()
                        ->dehydrated(false),

                    TextInput::make('email')
                        ->label('Email Address')
                        ->email()
                        ->required()
                        ->maxLength(255),

                    TextInput::make('phone')
                        ->label('Phone Number')
                        ->tel()
                        ->required()
                        ->maxLength(20),

                    Textarea::make('address')
                        ->label('Address')
                        ->rows(3)
                        ->maxLength(500),

                    FileUpload::make('profile_photo')
                        ->label('Profile Photo')
                        ->image()
                        ->directory('teacher-photos')
                        ->imageResizeMode('cover')
                        ->imageCropAspectRatio('1:1')
                        ->imageResizeTargetWidth('300')
                        ->imageResizeTargetHeight('300')
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('Identification & Banking')
                ->description('Update your identification and banking information')
                ->schema([
                    TextInput::make('nrc')
                        ->label('NRC Number')
                        ->maxLength(255)
                        ->disabled()
                        ->dehydrated(false),

                    TextInput::make('tpin')
                        ->label('TPIN')
                        ->maxLength(255)
                        ->disabled()
                        ->dehydrated(false),

                    TextInput::make('account_number')
                        ->label('Account Number')
                        ->maxLength(255),

                    TextInput::make('bank_name')
                        ->label('Bank Name')
                        ->maxLength(255),

                    TextInput::make('bank_branch')
                        ->label('Bank Branch')
                        ->maxLength(255),
                ])
                ->columns(2),

            Section::make('Additional Information')
                ->schema([
                    Textarea::make('biography')
                        ->label('Biography / Notes')
                        ->rows(4)
                        ->maxLength(1000)
                        ->columnSpanFull(),
                ]),
        ];
    }

    public function updateProfile(): void
    {
        $data = $this->form->getState();
        $teacher = $this->getTeacher();

        if (!$teacher) {
            Notification::make()
                ->title('Error')
                ->body('Teacher profile not found.')
                ->danger()
                ->send();
            return;
        }

        // Update teacher profile
        $teacher->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'address' => $data['address'],
            'nrc' => $data['nrc'],
            'tpin' => $data['tpin'],
            'account_number' => $data['account_number'],
            'bank_name' => $data['bank_name'],
            'bank_branch' => $data['bank_branch'],
            'biography' => $data['biography'],
            'profile_photo' => $data['profile_photo'],
        ]);

        // Update user email if changed
        if ($teacher->user && $teacher->user->email !== $data['email']) {
            $teacher->user->update([
                'email' => $data['email'],
                'name' => $data['name'],
            ]);
        }

        Notification::make()
            ->title('Profile Updated')
            ->body('Your profile has been updated successfully.')
            ->success()
            ->send();
    }

    public function downloadProfilePdf()
    {
        $teacher = $this->getTeacher();

        if (!$teacher) {
            Notification::make()
                ->title('Error')
                ->body('Teacher profile not found.')
                ->danger()
                ->send();
            return;
        }

        // Generate PDF
        $pdf = Pdf::loadView('pdf.teacher-profile', [
            'teacher' => $teacher,
            'user' => Auth::user(),
        ]);

        // Download the PDF
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'teacher-profile-' . $teacher->employee_id . '.pdf');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadProfile')
                ->label('Download Profile')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action('downloadProfilePdf'),

            Action::make('changePassword')
                ->label('Change Password')
                ->icon('heroicon-o-lock-closed')
                ->color('warning')
                ->form([
                    TextInput::make('current_password')
                        ->label('Current Password')
                        ->password()
                        ->required()
                        ->currentPassword()
                        ->revealable(),

                    TextInput::make('password')
                        ->label('New Password')
                        ->password()
                        ->required()
                        ->rule(Password::default())
                        ->revealable(),

                    TextInput::make('password_confirmation')
                        ->label('Confirm New Password')
                        ->password()
                        ->required()
                        ->same('password')
                        ->revealable(),
                ])
                ->action(function (array $data): void {
                    $user = Auth::user();

                    // Update password
                    $user->update([
                        'password' => Hash::make($data['password']),
                    ]);

                    Notification::make()
                        ->title('Password Updated')
                        ->body('Your password has been changed successfully.')
                        ->success()
                        ->send();
                }),
        ];
    }

    public static function canAccess(): bool
    {
        return in_array(auth()->user()?->role_id, RoleConstants::teaching()) ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return in_array(auth()->user()?->role_id, RoleConstants::teaching()) ?? false;
    }
}
