<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6 flex items-center justify-between gap-4 border-t pt-6 dark:border-gray-700">
            <div class="text-sm text-gray-500 dark:text-gray-400">
                @if($this->getSubheading())
                    <span class="flex items-center gap-2">
                        <x-heroicon-o-clock class="h-4 w-4" />
                        {{ $this->getSubheading() }}
                    </span>
                @endif
            </div>

            <div class="flex items-center gap-3">
                <x-filament::button
                    type="button"
                    color="gray"
                    wire:click="clearCache"
                    icon="heroicon-o-arrow-path"
                >
                    Clear Cache
                </x-filament::button>

                <x-filament::button
                    type="submit"
                    color="primary"
                    icon="heroicon-o-check"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-50"
                >
                    <span wire:loading.remove wire:target="save">Save Settings</span>
                    <span wire:loading wire:target="save">Saving...</span>
                </x-filament::button>
            </div>
        </div>
    </form>

    {{-- Loading overlay --}}
    <div
        wire:loading.flex
        wire:target="save"
        class="fixed inset-0 z-50 items-center justify-center bg-gray-900/50"
    >
        <div class="rounded-lg bg-white p-6 shadow-xl dark:bg-gray-800">
            <div class="flex items-center gap-3">
                <x-filament::loading-indicator class="h-6 w-6" />
                <span class="text-gray-700 dark:text-gray-200">Saving settings...</span>
            </div>
        </div>
    </div>
</x-filament-panels::page>
