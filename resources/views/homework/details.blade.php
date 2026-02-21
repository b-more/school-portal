<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $homework->title }} - Homework Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-sm">
        <div class="max-w-4xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <a href="/admin/student-dashboard" class="inline-flex items-center text-gray-600 hover:text-gray-900">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Dashboard
                </a>
                <span class="text-sm text-gray-500">{{ config('app.name', 'School Portal') }}</span>
            </div>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 py-8">
        <!-- Status Banner -->
        <div class="mb-6 rounded-xl overflow-hidden shadow-lg">
            <div class="px-6 py-4 {{ $homework->due_date->isPast() ? 'bg-red-500' : ($homework->due_date->isToday() ? 'bg-orange-500' : 'bg-blue-600') }}">
                <div class="flex items-center justify-between text-white">
                    <div class="flex items-center">
                        <i class="fas {{ $homework->due_date->isPast() ? 'fa-exclamation-circle' : 'fa-clock' }} text-2xl mr-3"></i>
                        <div>
                            <span class="font-bold text-lg">
                                @if($homework->due_date->isPast())
                                    Past Due
                                @elseif($homework->due_date->isToday())
                                    Due Today!
                                @else
                                    Due {{ $homework->due_date->diffForHumans() }}
                                @endif
                            </span>
                            <p class="text-sm opacity-90">{{ $homework->due_date->format('l, F d, Y \a\t g:i A') }}</p>
                        </div>
                    </div>
                    @if($submission)
                        <div class="bg-white/20 rounded-lg px-4 py-2 text-center">
                            <i class="fas fa-check-circle text-2xl"></i>
                            <span class="block text-sm font-medium">Submitted</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Homework Card -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6">
            <div class="p-6">
                <h1 class="text-2xl font-bold text-gray-900 mb-4">{{ $homework->title }}</h1>

                <!-- Tags -->
                <div class="flex flex-wrap gap-2 mb-6">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                        <i class="fas fa-book mr-2"></i>
                        {{ $homework->subject->name ?? 'N/A' }}
                    </span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                        <i class="fas fa-graduation-cap mr-2"></i>
                        {{ $homework->grade->name ?? 'N/A' }}
                    </span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                        <i class="fas fa-user mr-2"></i>
                        {{ $homework->assignedBy->name ?? 'Teacher' }}
                    </span>
                    @if($homework->max_score)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            <i class="fas fa-star mr-2"></i>
                            {{ $homework->max_score }} Points
                        </span>
                    @endif
                </div>

                <!-- Instructions -->
                <div class="border-t pt-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                        <i class="fas fa-clipboard-list text-blue-600 mr-2"></i>
                        Instructions
                    </h2>
                    @if($homework->description)
                        <div class="prose max-w-none text-gray-700 whitespace-pre-wrap leading-relaxed bg-gray-50 rounded-lg p-4">{{ $homework->description }}</div>
                    @else
                        <p class="text-gray-500 italic">No instructions provided.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Download Section -->
        @if($homework->homework_file)
        <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-paperclip text-blue-600 mr-2"></i>
                    Homework Resources
                </h2>
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center mr-4">
                                <i class="fas fa-file-alt text-xl text-blue-600"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900">Homework Document</h4>
                                <p class="text-sm text-gray-600">Download to view the complete homework</p>
                            </div>
                        </div>
                        <a href="{{ route('homework.download', $homework) }}"
                           target="_blank"
                           class="inline-flex items-center px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-150 ease-in-out shadow-sm">
                            <i class="fas fa-download mr-2"></i>
                            Download
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Submission Status -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-upload text-blue-600 mr-2"></i>
                    Your Submission
                </h2>

                @if($submission)
                    <div class="bg-green-50 border border-green-200 rounded-lg p-5">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <i class="fas fa-check-circle text-3xl text-green-500"></i>
                            </div>
                            <div class="ml-4 flex-1">
                                <h3 class="text-lg font-medium text-green-800">Homework Submitted!</h3>
                                <p class="text-sm text-green-700 mt-1">
                                    Submitted on {{ $submission->created_at->format('F d, Y \a\t g:i A') }}
                                </p>

                                @if($submission->marks !== null)
                                    <div class="mt-4 bg-white rounded-lg p-4 border border-green-200">
                                        <div class="flex items-center justify-between">
                                            <span class="text-gray-600">Grade:</span>
                                            <span class="text-3xl font-bold text-blue-600">
                                                {{ $submission->marks }}@if($homework->max_score)/{{ $homework->max_score }}@endif
                                            </span>
                                        </div>
                                        @if($submission->feedback)
                                            <div class="mt-3 pt-3 border-t">
                                                <span class="text-sm font-medium text-gray-600">Teacher's Feedback:</span>
                                                <p class="mt-1 text-gray-700">{{ $submission->feedback }}</p>
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <div class="mt-3 flex items-center text-yellow-600">
                                        <i class="fas fa-hourglass-half mr-2"></i>
                                        <span>Awaiting grading by teacher</span>
                                    </div>
                                @endif

                                @if($submission->submission_file)
                                    <div class="mt-4 flex items-center justify-between bg-gray-50 rounded-lg p-3">
                                        <div class="flex items-center text-gray-700">
                                            <i class="fas fa-file mr-2"></i>
                                            <span>Your submitted file</span>
                                        </div>
                                        <a href="{{ route('homework-submissions.download', $submission) }}"
                                           class="text-blue-600 hover:text-blue-800 font-medium text-sm">
                                            <i class="fas fa-eye mr-1"></i> View
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-file-upload text-5xl text-gray-300 mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No Submission Yet</h3>
                        <p class="text-gray-500 mb-6">You haven't submitted this homework yet.</p>

                        @if(!$homework->due_date->isPast())
                            <a href="/admin/homework-submissions/create?homework_id={{ $homework->id }}"
                               class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-150 ease-in-out shadow-sm">
                                <i class="fas fa-upload mr-2"></i>
                                Submit Homework
                            </a>
                        @else
                            <div class="inline-flex items-center px-4 py-2 bg-red-100 text-red-700 rounded-lg">
                                <i class="fas fa-exclamation-circle mr-2"></i>
                                This homework is past due
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Back Button -->
        <div class="text-center">
            <a href="/admin/student-dashboard"
               class="inline-flex items-center px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-lg transition duration-150 ease-in-out">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Dashboard
            </a>
        </div>
    </main>

    <!-- Footer -->
    <footer class="mt-12 py-6 border-t bg-white">
        <div class="max-w-4xl mx-auto px-4 text-center text-gray-500 text-sm">
            &copy; {{ date('Y') }} {{ config('app.name', 'School Portal') }}. All rights reserved.
        </div>
    </footer>
</body>
</html>
