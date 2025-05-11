<!-- resources/views/filament/pages/homework-details.blade.php -->
<div class="space-y-6">
    <!-- Homework Information Card -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="border-b border-gray-200 pb-4 mb-4">
            <h2 class="text-2xl font-bold text-gray-900">{{ $homework->title }}</h2>
            <div class="mt-2 flex flex-wrap gap-4 text-sm text-gray-600">
                <span><strong>Subject:</strong> {{ $homework->subject->name }}</span>
                <span><strong>Grade:</strong> {{ $homework->grade }}</span>
                <span><strong>Teacher:</strong> {{ $homework->assignedBy->name ?? 'Unknown' }}</span>
                <span><strong>Due Date:</strong> {{ $homework->due_date->format('F d, Y') }}</span>
            </div>
        </div>

        <!-- Description -->
        @if($homework->description)
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Instructions:</h3>
            <p class="text-gray-700 whitespace-pre-wrap">{{ $homework->description }}</p>
        </div>
        @endif

        <!-- Download Section -->
        @if($homework->homework_file)
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <svg class="h-8 w-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    <div>
                        <h4 class="font-medium text-gray-900">Homework Document</h4>
                        <p class="text-sm text-gray-600">Click to download the homework file</p>
                    </div>
                </div>
                <a href="{{ route('homework.download', $homework) }}"
                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-150 ease-in-out">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Download
                </a>
            </div>
        </div>
        @else
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <p class="text-yellow-800">No homework file has been uploaded yet.</p>
        </div>
        @endif
    </div>

    <!-- Student Information (for parents) -->
    @if(auth()->user()->hasRole('parent') && $student)
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Student Information</h3>
        <div class="text-sm text-gray-600">
            <p><strong>Student:</strong> {{ $student->name }}</p>
            <p><strong>Grade:</strong> {{ $student->grade }}</p>
        </div>
    </div>
    @endif

    <!-- Back Button -->
    <div class="flex justify-center pt-4">
        <a href="{{ route('filament.admin.pages.view-homework') }}"
           class="inline-flex items-center px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium rounded-lg transition duration-150 ease-in-out">
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Homework List
        </a>
    </div>
</div>
