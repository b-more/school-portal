<?php

namespace App\Filament\Resources\SchoolSettingsResource\Pages;

use App\Filament\Resources\SchoolSettingsResource;
use App\Models\SchoolSettings;
use Filament\Resources\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Cache;

class ManageSchoolSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = SchoolSettingsResource::class;
    protected static string $view = 'filament.resources.school-settings-resource.pages.manage-school-settings';
    protected static ?string $title = 'School Settings';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = SchoolSettings::getInstance();

        // Convert model to array and handle nested JSON fields
        $settingsArray = $settings->toArray();

        // Ensure JSON fields are properly formatted for the form
        $jsonFields = ['social_media_links', 'school_days', 'payment_methods', 'bank_details', 'mobile_money_details', 'custom_settings'];

        foreach ($jsonFields as $field) {
            if (isset($settingsArray[$field]) && is_string($settingsArray[$field])) {
                $settingsArray[$field] = json_decode($settingsArray[$field], true);
            }
        }

        $this->form->fill($settingsArray);
    }

    public function form(Form $form): Form
    {
        return SchoolSettingsResource::form($form)
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Settings')
                ->action('save')
                ->color('primary')
                ->icon('heroicon-o-check'),

            Action::make('reset_cache')
                ->label('Clear Cache')
                ->action('clearCache')
                ->color('gray')
                ->icon('heroicon-o-arrow-path')
                ->requiresConfirmation()
                ->modalHeading('Clear Settings Cache')
                ->modalDescription('This will clear the cached settings and reload from database.')
                ->modalSubmitActionLabel('Clear Cache'),
        ];
    }

    public function save(): void
    {
        $settings = SchoolSettings::first();

        if (!$settings) {
            $settings = new SchoolSettings();
        }

        $data = $this->form->getState();

        // Handle nested JSON data for bank_details and mobile_money_details
        if (isset($data['bank_details']) && is_array($data['bank_details'])) {
            // Filter out empty values
            $data['bank_details'] = array_filter($data['bank_details'], fn($value) => !empty($value));
        }

        if (isset($data['mobile_money_details']) && is_array($data['mobile_money_details'])) {
            $data['mobile_money_details'] = array_filter($data['mobile_money_details'], fn($value) => !empty($value));
        }

        // Add metadata
        $data['settings_last_updated_at'] = now();
        $data['settings_updated_by'] = auth()->id();

        // Update the settings
        $settings->fill($data);
        $settings->save();

        // Clear cache to ensure changes take effect immediately
        SchoolSettings::clearCache();
        Cache::forget('school_settings');

        // Also clear any other related caches
        Cache::flush(); // Clear all cache to be safe

        Notification::make()
            ->title('Settings Saved Successfully')
            ->body('All changes have been applied immediately.')
            ->success()
            ->duration(5000)
            ->send();
    }

    public function clearCache(): void
    {
        SchoolSettings::clearCache();
        Cache::forget('school_settings');

        Notification::make()
            ->title('Cache Cleared')
            ->body('Settings cache has been refreshed.')
            ->success()
            ->send();

        // Reload the form with fresh data
        $this->mount();
    }

    public function getSubheading(): ?string
    {
        $settings = SchoolSettings::first();

        if ($settings && $settings->settings_last_updated_at) {
            return 'Last updated: ' . $settings->settings_last_updated_at->format('d M Y, H:i');
        }

        return null;
    }
}
