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

class ManageSchoolSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = SchoolSettingsResource::class;
    protected static string $view = 'filament.resources.school-settings-resource.pages.manage-school-settings';
    protected static ?string $title = 'School Settings';

    public $data;

    public function mount(): void
    {
        $settings = SchoolSettings::getInstance();
        $this->form->fill($settings->toArray());
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
        ];
    }

    public function save(): void
    {
        $settings = SchoolSettings::getInstance();
        $data = $this->form->getState();

        $settings->update($data);

        Notification::make()
            ->title('School settings updated successfully')
            ->success()
            ->send();
    }
}
