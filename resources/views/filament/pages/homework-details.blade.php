<x-filament::page>
    <div class="space-y-6">
        <!-- Homework Header Information -->
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

                <div class="mt-4 grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-2">
                    <div class="sm:col-span-1">
                        <div class="text-sm font-medium text-gray-500">Assigned By</div>
                        <div class="mt-1 text-sm text-gray-900">{{ $homework->assignedBy->name }}</div>
                    </div>

                    <div class="sm:col-span-1">
                        <div class="text-sm font-medium text-gray-500">Submission Window</div>
                        <div class="mt-1 text-sm text-gray-900">
                            {{ $homework->submission_start ? $homework->submission_start->format('M j, Y g:i A') : 'Not specified' }}
                            to
                            {{ $homework->submission_end ? $homework->submission_end->format('M j, Y g:i A') : 'Not specified' }}

                            @if($isLate && $canSubmit)
                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Late Submission Period
                                </span>
                            @elseif(!$canSubmit)
                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    {{ $submissionStatus }}
                                </span>
                            @else
                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Open for Submission
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

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
                                    <x-heroicon-s-arrow-down-tray class="w-4 h-4 mr-2" />
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

        <!-- Submission Details -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900">
                    {{ $student->name }}'s Submission
                </h3>

                @if($submission)
                    <div class="mt-4 grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                        <div class="sm:col-span-1">
                            <div class="text-sm font-medium text-gray-500">Submitted</div>
                            <div class="mt-1 text-sm text-gray-900">
                                {{ $submission->submitted_at->format('F j, Y g:i A') }}

                                @if($submission->is_late)
                                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Late
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="sm:col-span-1">
                            <div class="text-sm font-medium text-gray-500">Status</div>
                            <div class="mt-1 text-sm text-gray-900">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $submission->status === 'graded' ? 'bg-green-100 text-green-800' :
                                    ($submission->status === 'submitted' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800') }}">
                                    {{ ucfirst($submission->status) }}
                                </span>
                            </div>
                        </div>

                        @if($submission->status === 'graded' && $submission->marks !== null)
                            <div class="sm:col-span-1">
                                <div class="text-sm font-medium text-gray-500">Score</div>
                                <div class="mt-1 text-sm text-gray-900">
                                    {{ $submission->marks }}/{{ $homework->max_score }}
                                    ({{ round(($submission->marks / $homework->max_score) * 100) }}%)
                                </div>
                            </div>
                        @endif

                        @if($submission->file_attachment)
                            <div class="sm:col-span-2">
                                <div class="text-sm font-medium text-gray-500">Submitted Files</div>
                                <div class="mt-2">
                                    <ul class="border border-gray-200 rounded-md divide-y divide-gray-200">
                                        @foreach(json_decode($submission->file_attachment) as $index => $file)
                                            <li class="pl-3 pr-4 py-3 flex items-center justify-between text-sm">
                                                <div class="w-0 flex-1 flex items-center">
                                                    <svg class="flex-shrink-0 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M8 4a3 3 0 00-3 3v4a5 5 0 0010 0V7a1 1 0 112 0v4a7 7 0 11-14 0V7a5 5 0 0110 0v4a3 3 0 11-6 0V7a1 1 0 012 0v4a1 1 0 102 0V7a3 3 0 00-3-3z" clip-rule="evenodd" />
                                                    </svg>
                                                    <span class="ml-2 flex-1 w-0 truncate">
                                                        {{ basename($file) }}
                                                    </span>
                                                </div>
                                                <div class="ml-4 flex-shrink-0">
                                                    <a href="{{ Storage::url($file) }}" class="font-medium text-indigo-600 hover:text-indigo-500" target="_blank">
                                                        Download
                                                    </a>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif

                        @if($submission->content)
                            <div class="sm:col-span-2">
                                <div class="text-sm font-medium text-gray-500">Student Comments</div>
                                <div class="mt-1 text-sm text-gray-900 border border-gray-200 rounded-md p-3 bg-gray-50">
                                    {!! nl2br(e($submission->content)) !!}
                                </div>
                            </div>
                        @endif

                        @if($submission->status === 'graded' && $submission->feedback)
                            <div class="sm:col-span-2">
                                <div class="text-sm font-medium text-gray-500">Teacher Feedback</div>
                                <div class="mt-1 text-sm text-gray-900 border border-gray-200 rounded-md p-3 bg-blue-50">
                                    {!! nl2br(e($submission->feedback)) !!}
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="mt-4">
                        <div class="rounded-md bg-yellow-50 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800">No Submission Yet</h3>
                                    <div class="mt-2 text-sm text-yellow-700">
                                        <p>{{ $student->name }} has not submitted this homework assignment yet.</p>

                                        @if($canSubmit)
                                            <p class="mt-1">
                                                Submission is currently open. Please have your child log in to their student account to submit this homework.
                                            </p>

                                            @if($isLate)
                                                <p class="mt-1 font-medium">
                                                    Note: The regular deadline has passed, but late submissions are still being accepted until {{ $homework->late_submission_deadline->format('F j, Y g:i A') }}.
                                                </p>
                                            @endif
                                        @else
                                            <p class="mt-1">
                                                {{ $submissionStatus }}.
                                                @if($homework->submission_end && now() > $homework->submission_end)
                                                    The deadline was {{ $homework->submission_end->format('F j, Y g:i A') }}.
                                                @elseif($homework->submission_start && now() < $homework->submission_start)
                                                    Submissions will open on {{ $homework->submission_start->format('F j, Y g:i A') }}.
                                                @endif
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-filament::page>
