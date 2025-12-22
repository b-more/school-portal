<x-filament::page>
    <div class="space-y-6">
        <!-- Homework Information -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">{{ $homework->title }}</h2>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">
                            {{ $homework->subject->name ?? 'No Subject' }} | {{ $homework->grade }}
                        </p>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-medium text-gray-500">Due Date</div>
                        <div class="text-base text-gray-900">{{ $homework->due_date->format('F j, Y') }}</div>
                        <div class="text-xs text-gray-500">{{ $homework->due_date->diffForHumans() }}</div>
                    </div>
                </div>

                @if($isLate)
                    <div class="mt-4 rounded-md bg-yellow-50 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800">Late Submission</h3>
                                <div class="mt-2 text-sm text-yellow-700">
                                    <p>The regular deadline for this assignment has passed. Your submission will be marked as late.</p>
                                    <p class="mt-1">Late submissions will be accepted until {{ $homework->late_submission_deadline->format('F j, Y g:i A') }}.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="mt-4">
                    <div class="text-sm font-medium text-gray-500">Description</div>
                    <div class="mt-2 prose prose-sm max-w-none text-gray-900">
                        {!! nl2br(e($homework->description)) !!}
                    </div>
                </div>

                @if($homework->submission_instructions)
                    <div class="mt-4">
                        <div class="text-sm font-medium text-gray-500">Submission Instructions</div>
                        <div class="mt-2 prose prose-sm max-w-none text-gray-900">
                            {!! nl2br(e($homework->submission_instructions)) !!}
                        </div>
                    </div>
                @endif

                <div class="mt-4">
                    <div class="text-sm font-medium text-gray-500">Resources</div>
                    <div class="mt-2">
                        @if($homework->homework_file)
                            <div class="mb-2">
                                <a href="{{ route('homework.download', $homework) }}"
                                   class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                   target="_blank">
                                    <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Download Main Homework Document
                                </a>
                            </div>
                        @else
                            <p class="text-sm text-gray-500">No main document uploaded.</p>
                        @endif

                        @if(!empty($homework->file_attachment))
                            <div class="mt-2">
                                <p class="text-sm font-medium">Additional Resources:</p>
                                <ul class="mt-1 list-disc list-inside text-sm text-gray-600">
                                    @foreach(json_decode($homework->file_attachment) as $index => $file)
                                        <li>
                                            <a href="{{ Storage::url($file) }}" class="text-indigo-600 hover:text-indigo-900" target="_blank">
                                                Resource {{ $index + 1 }} ({{ pathinfo($file, PATHINFO_EXTENSION) }})
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Submission Form -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900">Submit Your Work</h3>

                {{ $this->form }}

                <div class="mt-6 flex justify-end">
                    {{ $this->getAction('submit') }}
                </div>
            </div>
        </div>
    </div>
</x-filament::page>
