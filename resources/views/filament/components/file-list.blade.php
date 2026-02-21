<!-- resources/views/filament/components/file-list.blade.php -->
@if($files && count($files) > 0)
    <div class="space-y-2">
        @foreach($files as $file)
            <div class="flex items-center space-x-3 p-2 bg-gray-50 rounded-md">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <a href="{{ $file['url'] }}" target="_blank" class="text-sm text-blue-600 hover:text-blue-800 hover:underline flex-1">
                    {{ $file['name'] }}
                </a>
                <a href="{{ $file['url'] }}" download class="text-sm text-gray-500 hover:text-gray-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                </a>
            </div>
        @endforeach
    </div>
@else
    <p class="text-sm text-gray-500">No files attached</p>
@endif
