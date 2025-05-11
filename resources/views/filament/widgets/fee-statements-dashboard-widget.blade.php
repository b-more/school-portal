<x-filament-widgets::widget>
    <x-filament::section>
        <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg p-6 text-white shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-xl font-semibold">Fee Statements Generator</h3>
                    <p class="text-orange-100 text-sm">Generate comprehensive fee payment reports and statements</p>
                </div>
                <svg class="w-12 h-12 text-orange-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <a href="/fee-statements" class="bg-white/20 hover:bg-white/30 backdrop-blur-sm rounded-md p-3 transition-all duration-200 inline-flex flex-col items-center text-center">
                    <svg class="w-6 h-6 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <span class="text-sm font-medium">Individual</span>
                </a>

                <a href="/fee-statements" class="bg-white/20 hover:bg-white/30 backdrop-blur-sm rounded-md p-3 transition-all duration-200 inline-flex flex-col items-center text-center">
                    <svg class="w-6 h-6 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    <span class="text-sm font-medium">By Grade</span>
                </a>

                <a href="/fee-statements" class="bg-white/20 hover:bg-white/30 backdrop-blur-sm rounded-md p-3 transition-all duration-200 inline-flex flex-col items-center text-center">
                    <svg class="w-6 h-6 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9m2 0h4m6 11a2 2 0 01-2-2V9a2 2 0 00-2-2h-2m-4-3H9m2 0h4m-6 11a2 2 0 01-2-2V9.236a2 2 0 00-1.447-1.923L6 6l-4-1-4 1 .447 2.236A2 2 0 000 9.236V13a2 2 0 002 2h4z"></path>
                    </svg>
                    <span class="text-sm font-medium">By Section</span>
                </a>

                <a href="/fee-statements/summary" class="bg-white/20 hover:bg-white/30 backdrop-blur-sm rounded-md p-3 transition-all duration-200 inline-flex flex-col items-center text-center">
                    <svg class="w-6 h-6 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <span class="text-sm font-medium">Summary</span>
                </a>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
