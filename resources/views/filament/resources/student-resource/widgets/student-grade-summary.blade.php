@php
    $data = $this->getGradeData();
    $grades = $data['grades'];
    $totalBoys = $data['totalBoys'];
    $totalGirls = $data['totalGirls'];
    $totalAll = $data['totalAll'];
@endphp

<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-heroicon-o-academic-cap class="w-5 h-5 text-primary-500" />
                <span>Student Enrollment by Grade</span>
            </div>
        </x-slot>

        <x-slot name="description">
            Active students: {{ number_format($totalAll) }} total &middot; {{ number_format($totalBoys) }} boys &middot; {{ number_format($totalGirls) }} girls
        </x-slot>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="text-left py-2 px-3 font-semibold text-gray-600 dark:text-gray-400">Grade</th>
                        <th class="text-center py-2 px-3 font-semibold text-blue-600 dark:text-blue-400">Boys</th>
                        <th class="text-center py-2 px-3 font-semibold text-pink-600 dark:text-pink-400">Girls</th>
                        <th class="text-center py-2 px-3 font-semibold text-gray-600 dark:text-gray-400">Total</th>
                        <th class="text-left py-2 px-3 font-semibold text-gray-600 dark:text-gray-400" style="min-width: 140px;">Distribution</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($grades as $grade)
                        @php
                            $boysPercent = $grade['total'] > 0 ? round(($grade['boys'] / $grade['total']) * 100) : 0;
                            $girlsPercent = 100 - $boysPercent;
                        @endphp
                        <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900/50 transition">
                            <td class="py-2 px-3 font-medium text-gray-900 dark:text-gray-100">{{ $grade['grade_name'] }}</td>
                            <td class="py-2 px-3 text-center text-blue-700 dark:text-blue-300 font-medium">{{ $grade['boys'] }}</td>
                            <td class="py-2 px-3 text-center text-pink-700 dark:text-pink-300 font-medium">{{ $grade['girls'] }}</td>
                            <td class="py-2 px-3 text-center font-bold text-gray-900 dark:text-white">{{ $grade['total'] }}</td>
                            <td class="py-2 px-3">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 h-2.5 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden flex">
                                        <div class="h-full bg-blue-500 rounded-l-full" style="width: {{ $boysPercent }}%"></div>
                                        <div class="h-full bg-pink-500 rounded-r-full" style="width: {{ $girlsPercent }}%"></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900/50">
                        <td class="py-2.5 px-3 font-bold text-gray-900 dark:text-white">Total</td>
                        <td class="py-2.5 px-3 text-center font-bold text-blue-700 dark:text-blue-300">{{ $totalBoys }}</td>
                        <td class="py-2.5 px-3 text-center font-bold text-pink-700 dark:text-pink-300">{{ $totalGirls }}</td>
                        <td class="py-2.5 px-3 text-center font-bold text-gray-900 dark:text-white">{{ $totalAll }}</td>
                        <td class="py-2.5 px-3">
                            @php
                                $totalBoysPercent = $totalAll > 0 ? round(($totalBoys / $totalAll) * 100) : 0;
                                $totalGirlsPercent = 100 - $totalBoysPercent;
                            @endphp
                            <div class="flex items-center gap-2">
                                <div class="flex-1 h-2.5 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden flex">
                                    <div class="h-full bg-blue-500 rounded-l-full" style="width: {{ $totalBoysPercent }}%"></div>
                                    <div class="h-full bg-pink-500 rounded-r-full" style="width: {{ $totalGirlsPercent }}%"></div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
