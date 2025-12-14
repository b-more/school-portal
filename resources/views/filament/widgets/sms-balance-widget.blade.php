<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center gap-x-3">
            <div class="flex-shrink-0">
                <div class="rounded-full p-3 {{ $isLow ? 'bg-danger-100 dark:bg-danger-500/20' : 'bg-success-100 dark:bg-success-500/20' }}">
                    <x-heroicon-o-chat-bubble-left-right class="h-6 w-6 {{ $isLow ? 'text-danger-600 dark:text-danger-400' : 'text-success-600 dark:text-success-400' }}" />
                </div>
            </div>
            <div class="flex-1">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    SMS Credit Balance
                </h3>
                <div class="mt-1 flex items-baseline gap-x-2">
                    <span class="text-2xl font-semibold tracking-tight {{ $isLow ? 'text-danger-600 dark:text-danger-400' : 'text-gray-950 dark:text-white' }}">
                        {{ number_format($balance) }} credits
                    </span>
                    @if(!$isActive)
                        <span class="inline-flex items-center rounded-md bg-danger-50 dark:bg-danger-400/10 px-2 py-1 text-xs font-medium text-danger-700 dark:text-danger-400 ring-1 ring-inset ring-danger-600/10">
                            Disabled
                        </span>
                    @elseif($isLow)
                        <span class="inline-flex items-center rounded-md bg-warning-50 dark:bg-warning-400/10 px-2 py-1 text-xs font-medium text-warning-700 dark:text-warning-400 ring-1 ring-inset ring-warning-600/10">
                            Low Balance
                        </span>
                    @endif
                </div>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    ~{{ number_format($estimatedSms) }} SMS remaining
                </p>
            </div>
            <div class="flex-shrink-0">
                <a href="{{ route('filament.admin.resources.sms-credits.index') }}"
                   class="inline-flex items-center justify-center gap-1 font-medium rounded-lg border transition-colors focus:outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset min-h-[2rem] px-3 text-sm text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 focus:ring-primary-600 focus:ring-offset-white dark:focus:ring-offset-gray-800">
                    <x-heroicon-m-plus class="h-4 w-4" />
                    Top Up
                </a>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
