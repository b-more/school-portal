<!-- resources/views/filament/dashboard/partials/activity-item.blade.php -->
@switch($type)
    @case('students')
        <li class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <div class="flex-shrink-0 mr-3">
                @if(isset($item->profile_photo) && $item->profile_photo)
                    <img src="{{ Storage::url($item->profile_photo) }}" alt="{{ $item->name }}" class="w-10 h-10 rounded-full">
                @else
                    <div class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center">
                        <span class="text-gray-600 dark:text-gray-400 font-bold">{{ substr($item->name ?? 'S', 0, 1) }}</span>
                    </div>
                @endif
            </div>
            <div class="flex-1">
                <p class="font-medium text-gray-900 dark:text-white">{{ $item->name ?? 'Unknown' }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $item->grade ?? 'N/A' }} | {{ isset($item->created_at) ? $item->created_at->diffForHumans() : 'N/A' }}</p>
            </div>
            <div class="ml-auto">
                @if(Route::has('filament.admin.resources.students.edit') && isset($item->id))
                    <a href="{{ route('filament.admin.resources.students.edit', ['record' => $item]) }}" class="text-primary-500 hover:underline text-sm">View</a>
                @endif
            </div>
        </li>
        @break

    @case('payments')
        <li class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <div class="flex-shrink-0 mr-3">
                <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center">
                    <x-heroicon-o-banknotes class="w-5 h-5 text-green-500 dark:text-green-400" />
                </div>
            </div>
            <div class="flex-1">
                <p class="font-medium text-gray-900 dark:text-white">{{ $item->student->name ?? 'Unknown Student' }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    ZMW {{ number_format($item->amount_paid ?? 0, 2) }} |
                    {{ isset($item->payment_date) ? $item->payment_date->format('M d, Y') : 'No date' }}
                </p>
            </div>
            <div class="ml-auto">
                @if(Route::has('filament.admin.resources.student-fees.edit') && isset($item->id))
                    <a href="{{ route('filament.admin.resources.student-fees.edit', ['record' => $item]) }}" class="text-primary-500 hover:underline text-sm">View</a>
                @endif
            </div>
        </li>
        @break

    @case('submissions')
        <li class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <div class="flex-shrink-0 mr-3">
                <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                    <x-heroicon-o-document-text class="w-5 h-5 text-blue-500 dark:text-blue-400" />
                </div>
            </div>
            <div class="flex-1">
                <p class="font-medium text-gray-900 dark:text-white">{{ $item->student->name ?? 'Unknown Student' }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $item->homework->title ?? 'Unknown Homework' }} |
                    {{ isset($item->created_at) ? $item->created_at->diffForHumans() : 'N/A' }}
                </p>
            </div>
            <div class="ml-auto">
                @if(Route::has('filament.admin.resources.homework-submissions.edit') && isset($item->id))
                    <a href="{{ route('filament.admin.resources.homework-submissions.edit', ['record' => $item]) }}" class="text-primary-500 hover:underline text-sm">View</a>
                @endif
            </div>
        </li>
        @break

    @default
        <li class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <div class="flex-shrink-0 mr-3">
                <div class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                    <x-heroicon-o-clock class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                </div>
            </div>
            <div class="flex-1">
                <p class="font-medium text-gray-900 dark:text-white">{{ $item->name ?? 'Unknown' }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $item->description ?? 'No description' }}</p>
            </div>
        </li>
@endswitch
