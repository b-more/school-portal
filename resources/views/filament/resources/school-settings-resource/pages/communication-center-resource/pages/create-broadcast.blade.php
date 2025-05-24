<x-filament::page>
    <form wire:submit.prevent="create">
        {{ $this->form }}

        <div class="flex justify-end mt-6">
            <x-filament::button type="submit">
                Create Broadcast
            </x-filament::button>
        </div>
    </form>
</x-filament::page>
